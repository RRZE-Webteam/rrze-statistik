<?php

namespace RRZE\Statistik;

defined('ABSPATH') || exit;

/**
 * Collects and processes data
 */
class Data
{
    public static function setTransients()
    {
        if (is_admin()) {
            $screen = get_current_screen();
            if ($screen->id == "dashboard") {
                if(!get_transient('rrze_statistik_data_webalizer_hist')) {
                    Self:: updateData();
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
    public static function updateDataWeekly()
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
            update_option('rrze_statistik_url_dataset', $processed_data);
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
            return false;
        } else if (strlen($data_body) === 0) {
            return false;
        } else if (strpos($data_body, "could not be found on this server") !== false) {
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

            if (
                strpos($array_splitted[1], "wp-includes")
                or strpos($array_splitted[1], "wp-content")
                or strpos($array_splitted[1], "robots")
                or strpos($array_splitted[1], "wp-admin")
                or strpos($array_splitted[1], "xml")
                or strpos($array_splitted[1], ".css")
                or strpos($array_splitted[1], "module.php")
            ) {
            } elseif (
                strpos($array_splitted[1], ".jpg")
                or strpos($array_splitted[1], ".jpeg")
                or strpos($array_splitted[1], ".gif")
                or strpos($array_splitted[1], ".png")
                or strpos($array_splitted[1], ".svg")
                or strpos($array_splitted[1], ".webp")
                or strpos($array_splitted[1], ".ico")
                or strpos($array_splitted[1], ".bmp")
                or strpos($array_splitted[1], ".tiff")
                or strpos($array_splitted[1], ".tif")
                or strpos($array_splitted[1], ".psd")
                or strpos($array_splitted[1], ".ai")
                or strpos($array_splitted[1], ".eps")
            ) {
                array_push($image_files, $array_splitted);
            } elseif (
                strpos($array_splitted[1], ".pdf")
                or strpos($array_splitted[1], ".docx")
                or strpos($array_splitted[1], ".ppt")
                or strpos($array_splitted[1], ".pptx")
                or strpos($array_splitted[1], ".xls")
                or strpos($array_splitted[1], ".xlsx")
                or strpos($array_splitted[1], ".doc")
                or strpos($array_splitted[1], ".zip")
                or strpos($array_splitted[1], ".rar")
                
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
