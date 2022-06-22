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
                    Self::updateData();
                }
                if (!get_transient('rrze_statistik_data_url')) {
                    Self::updateUrlData();
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
        $data_body = Self::fetchDataBody($url);
        $validation = Self::validateData($data_body);
        if ($validation === false) {
            return false;
        } else {
            $data = Self::processDataBody($data_body);
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
        $data_body = Self::fetchDataBody($url);
        $validation = Self::validateData($data_body);

        if ($validation === false) {
            return false;
        } else {
            $data = substr($data_body, 0, 9999);

            $processed_data = Self::processUrlDataBody($data);
            set_transient('rrze_statistik_data_url', $processed_data, 12 * HOUR_IN_SECONDS);
            return true;
        }
    }

    /**
     * Fetches body $url from statistiken.rrze.fau.de
     *
     * @param string $url
     * @return string
     */
    public static function fetchDataBody($url)
    {
        $cachable = wp_remote_get(esc_url_raw($url));
        $cachable_body = wp_remote_retrieve_body($cachable);
        return $cachable_body;
    }

    /**
     * Evaluates if fetch was successful and if data is usable
     *
     * @param string $data_body
     * @return boolean
     */
    public static function validateData($data_body)
    {
        if (strpos($data_body, "Forbidden") !== false) {
            do_action('rrze.log.info', 'RRZE Statistik | Statistiken.rrze.fau.de forbidden fetch response body');
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

    /**
     * Converts .tab separated data in associative array for later use. in Cronjob, weekly
     *
     * @param [type] $data_body
     * @return array
     */
    public static function processUrlDataBody($data_body)
    {
        $data_trim = rtrim($data_body, " \n\r\t\v");
        $array = preg_split("/\r\n|\n|\r/", $data_trim);
        $image_files = [];
        $sites = [];
        $pdf_files = [];

        $output = [];
        foreach ($array as $value) {
            $array_splitted = preg_split("/	|( 	)/", $value);
            \array_splice($array_splitted, 1, -1);
            //Following file extensions are ignored
            if (
                strpos($array_splitted[1], "wp-includes") !== false
                || strpos($array_splitted[1], "wp-content") !== false
                || strpos($array_splitted[1], "wp-json") !== false
                || strpos($array_splitted[1], "wp-admin") !== false
                || strpos($array_splitted[1], "robots") !== false
                || strpos($array_splitted[1], "xml") !== false
                || strpos($array_splitted[1], "module.php") !== false
                || strpos($array_splitted[1], ".css") !== false
                || strpos($array_splitted[1], ".js") !== false
                || strpos($array_splitted[1], ".json") !== false
            ) {
            //Following file extensions are listed below sites in Dashboard
            } elseif (
                strpos($array_splitted[1], ".jpg") !== false
                || strpos($array_splitted[1], ".jpeg") !== false
                || strpos($array_splitted[1], ".gif") !== false
                || strpos($array_splitted[1], ".png") !== false
                || strpos($array_splitted[1], ".svg") !== false
                || strpos($array_splitted[1], ".webp") !== false
                || strpos($array_splitted[1], ".ico") !== false
                || strpos($array_splitted[1], ".bmp") !== false
                || strpos($array_splitted[1], ".tiff") !== false
                || strpos($array_splitted[1], ".tif") !== false
                || strpos($array_splitted[1], ".psd") !== false
                || strpos($array_splitted[1], ".ai") !== false
                || strpos($array_splitted[1], ".eps") !== false
            ) {
                array_push($image_files, $array_splitted);
            //Following file extensions are listed below documents in Dashboard
            } elseif (
                strpos($array_splitted[1], ".pdf") !== false
                || strpos($array_splitted[1], ".docx") !== false
                || strpos($array_splitted[1], ".ppt") !== false
                || strpos($array_splitted[1], ".pptx") !== false
                || strpos($array_splitted[1], ".xls") !== false
                || strpos($array_splitted[1], ".xlsx") !== false
                || strpos($array_splitted[1], ".doc") !== false
                || strpos($array_splitted[1], ".zip") !== false
                || strpos($array_splitted[1], ".rar") !== false

            ) {
                array_push($pdf_files, $array_splitted);
            } else {
                array_push($sites, $array_splitted);
            }
        }
        //if last array item has no trailing slash, remove it
        if (substr($sites[count($sites) - 1][1], -1) !== "/") {
            array_pop($sites);
        }
        array_pop($image_files);
        array_pop($pdf_files);


        //check if value isNull
        is_null($sites) ? $sites = [] : $sites;
        is_null($image_files) ? $image_files = [] : $image_files;
        is_null($pdf_files) ? $pdf_files = [] : $pdf_files;

        $output = [array_slice($sites, 0, 10), array_slice($image_files, 0, 10), array_slice($pdf_files, 0, 5)];
        return ($output);
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
        $data_trim = rtrim($data_body, " \n\r\t\v");
        $array = preg_split("/\r\n|\n|\r/", $data_trim);
        $output = [];
        foreach ($array as $value) {
            array_push($output, array_combine($keymap, preg_split("/ /", $value)));
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
