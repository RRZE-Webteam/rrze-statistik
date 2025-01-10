<?php

namespace RRZE\Statistik;

defined('ABSPATH') || exit;

/**
 * Collects and processes data
 */
class Data
{

    public function __construct()
    {
        add_action('current_screen',  [$this, 'setTransients']);
    }

    public static function setTransients()
    {
        if (is_admin()) {
            $screen = get_current_screen();
            if ($screen->id == "dashboard") {
                if (!get_transient('rrze_statistik_data_webalizer_hist')) {
                    self::updateData();
                }
                if (!get_transient('rrze_statistik_data_url')) {
                    self::updateUrlData();
                }
            }
        }
    }


    /**
     * Cron Job, hourly, data for webalizer.hist from statistiken.rrze.fau.de
     *
     * @return boolean
     */
    public static function updateData()
    {
        // Fetch Dataset Webalizer.hist
        $url = Analytics::retrieveSiteUrl('webalizer.hist');
        $data_body = self::fetchDataBody($url);
        $validation = self::validateData($data_body);
        if ($validation === false) {
            return false;
        } else {
            $data = self::processDataBody($data_body);
            array_pop($data);
            set_transient('rrze_statistik_data_webalizer_hist', $data, 6 * HOUR_IN_SECONDS);
            return true;
        }
    }

    /**
     * Cron job, weekly, popular sites and image data fetch from statistiken.rrze.fau.de
     *
     * @return boolean
     */
    public static function updateUrlData()
    {
        // Fetch Dataset
        $url = Analytics::retrieveSiteUrl('url');
        $data_body = self::fetchDataBody($url);
        $validation = self::validateData($data_body);

        if ($validation === false) {
            return false;
        } else {
            $data = substr($data_body, 0, 9999);

            $processed_data = self::processUrlDataBody($data);
            set_transient('rrze_statistik_data_url', $processed_data, 12 * HOUR_IN_SECONDS);
            return true;
        }
    }

    /**
     * Fetches body $url from statistiken.rrze.fau.de and aborts if the file is too large
     *
     * @param string $url
     * @return string|false
     */
    public static function fetchDataBody($url)
    {
        $response = wp_remote_head($url); // Initial HEAD-Request, um die Header zu prüfen
        if (is_wp_error($response)) {
            Helper::debug('RRZE Statistik | Failed to fetch headers: ' . $response->get_error_message());
            return false;
        }

        // Check the HTTP Status Code
        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code == 403) {
            Helper::debug('RRZE Statistik | HTTP 403 Forbidden: Access denied.');
            return false;
        } elseif ($status_code == 404) {
            Helper::debug('RRZE Statistik | HTTP 404 Not Found: Resource unavailable.');
            return false;
        } elseif ($status_code >= 400) {
            Helper::debug('RRZE Statistik | HTTP Error ' . $status_code . ': Aborting fetch.');
            return false;
        }

        // Check the Data Size
        $content_length = wp_remote_retrieve_header($response, 'content-length');
        Helper::debug(wp_remote_retrieve_response_code($response));
        if ($content_length && $content_length > 12 * 1024 * 1024) { // Set Size Limit
            Helper::debug('RRZE Statistik | File size exceeds the 12 MB limit. Aborting fetch.');
            return false;
        }

        // Only Fetch Content, if Size Limit of 12 MB is not exceeded
        $response = wp_remote_get(esc_url_raw($url), ['timeout' => 10]); // Set Timeout
        if (is_wp_error($response)) {
            Helper::debug('RRZE Statistik | Failed to fetch data: ' . $response->get_error_message());
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        if (empty($body)) {
            Helper::debug('rrze.log.error', 'RRZE Statistik | Empty response body.');
            return false;
        }

        return $body;
    }

    /**
     * Evaluates if fetch was successful and if data is usable
     *
     * @param string $data_body
     * @return boolean
     */
    public static function validateData($data_body)
    {
        if (null === $data_body) {
            do_action('rrze.log.info', 'RRZE Statistik | Data body is null');
            return false;
        } else if (strpos($data_body, "Forbidden") !== false) {
            do_action('rrze.log.info', 'RRZE Statistik | Statistiken.rrze.fau.de forbidden fetch response body');
            return false;
        } else if (strpos($data_body, "Unavailable") !== false) {
            do_action('rrze.log.info', 'RRZE Statistik | Statistiken.rrze.fau.de unavailable fetch response body');
            return false;
        } else if (strlen($data_body) === 0) {
            do_action('rrze.log.info', 'RRZE Statistik | Statistiken.rrze.fau.de empty fetch response body');
            return false;
        } else if (strpos($data_body, "could not be found on this server") !== false) {
            do_action('rrze.log.info', 'RRZE Statistik | Statistiken.rrze.fau.de fetch response body: Site not found on this server');
            return false;
        } else if (strpos($data_body, "not found") !== false) {
            do_action('rrze.log.info', 'RRZE Statistik | Statistiken.rrze.fau.de forbidden fetch response body: Site not found on this server');
            return false;
        } else {
            return true;
        }
    }

    private static function generateRows($data_trim)
{
    foreach (explode("\n", $data_trim) as $row) {
        yield $row;
    }
}

    /**
     * Converts .tab separated data in associative array for later use. in Cronjob, weekly
     *
     * @param [type] $data_body
     * @return array
     */
    public static function processUrlDataBody($data_body)
    {
        // Trim and split the input data
        $data_trim = rtrim($data_body, " \n\r\t\v");
        if (empty($data_trim)) {
            return [[], [], []]; // Return empty arrays if no data
        }
        $rows = explode("\n", $data_trim);

        // Precompile patterns for better performance
        $ignore_patterns = [
            "wp-includes",
            "wp-content",
            "wp-json",
            "wp-admin",
            "robots",
            "xml",
            "module.php",
            ".css",
            ".js",
            ".json",
            "/feed"
        ];

        $image_patterns = [
            ".jpg",
            ".jpeg",
            ".gif",
            ".png",
            ".svg",
            ".webp",
            ".ico",
            ".bmp",
            ".tiff",
            ".tif",
            ".psd",
            ".ai",
            ".eps"
        ];

        $document_patterns = [
            ".pdf",
            ".docx",
            ".ppt",
            ".pptx",
            ".xls",
            ".xlsx",
            ".doc",
            ".zip",
            ".rar"
        ];

        // Initialize result arrays
        $sites = [];
        $image_files = [];
        $pdf_files = [];

        // Set iteration and time limits
        $max_iterations = 10000; // Maximum rows to process
        $start_time = microtime(true);
        $time_limit = 5; // Max processing time in seconds

        $index = 0;
        foreach (self::generateRows($data_trim) as $row) {
            $index++;
            // Stop processing if iteration or time limits are exceeded
            if ($index >= $max_iterations || (microtime(true) - $start_time) > $time_limit) {
                break;
            }

            $columns = preg_split("/\t+/", $row, -1, PREG_SPLIT_NO_EMPTY);
            unset($columns[4]);
            unset($columns[3]);
            unset($columns[2]);
            unset($columns[1]);
            $columns = array_values($columns);
            if (count($columns) < 2) {
                continue;
            }

            $url = $columns[1];

            // Check if the URL matches any ignore patterns
            if (self::matchesAnyPattern($url, $ignore_patterns)) {
                continue;
            }

            // Categorize the URL into images, documents, or other sites
            if (self::matchesAnyPattern($url, $image_patterns)) {
                $image_files[] = $columns;
            } elseif (self::matchesAnyPattern($url, $document_patterns)) {
                $pdf_files[] = $columns;
            } else {
                $sites[] = $columns;
            }

            // Stop processing if enough items are collected
            if (count($sites) >= 10 && count($image_files) >= 10 && count($pdf_files) >= 5) {
                break;
            }
        }

        return [
            array_slice($sites, 0, 10),
            array_slice($image_files, 0, 10),
            array_slice($pdf_files, 0, 5),
        ];
    }

    /**
     * Checks if a string matches any pattern in the given array
     *
     * @param string $text
     * @param array $patterns
     * @return bool
     */
    private static function matchesAnyPattern($text, $patterns)
    {
        return array_filter($patterns, fn($pattern) => strpos($text, $pattern) !== false);
    }


    /**
     * Transforms webalizer.hist into Array and keys it with associated keymap.
     *
     * @param string $data_body
     * @return array
     */
    public static function processDataBody($data_body)
    {
        $keymap = array(
            'month',
            'year',
            'hits',
            'files',
            'hosts',
            'kbytes',
            'month_count',
            'recorded_days',
            'pages',
            'visits',
        );
        $data_trim = trim($data_body, " \n\r\t\v");
        $array = preg_split("/\r\n|\n|\r/", $data_trim);
        $output = [];

        // Helper::debug($data_body);

        foreach ($array as $value) {
            $splittedValue = preg_split("/ /", $value);

            if (count($keymap) !== count($splittedValue)) {
                Helper::debug('RRZE Statistik | Statistiken.rrze.fau.de fetch response body: The server is temporarily unable to service your request due to maintenance downtime or capacity problems. Please try again later.');
                continue;
            }

            array_push($output, array_combine($keymap, $splittedValue));
        }

        return $output;
    }


    /**
     * Uses a set of functions to fetch webalizer.hist, process the data, set description 
     * tags for the charts and send the combined information to the JS file
     *
     * @param string $url
     * @return boolean returns true if successful.
     */
    public static function processLinechartDataset($url)
    {
        $data = get_transient('rrze_statistik_data_webalizer_hist');

        if ($data === false) {
            Transfer::sendToJs('undefined', 'undefined', 'undefined', 'undefined', 'undefined');
            return false;
        } else {
            Transfer::sendToJs($data, Language::getAbscissa(), Language::getLanguagePackage());
            return true;
        }
    }
}
