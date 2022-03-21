<?php

namespace RRZE\Statistik;

defined('ABSPATH') || exit;

/**
 * Collects and processes data
 */
class Data
{
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
        $isOptionSet = get_option('rrze_statistik_webalizer_hist_data');
        if ($validation === false) {
            return false;
        } else if (!$isOptionSet) {
            $data = Self::processDataBody($data_body);
            array_pop($data);
            update_option('rrze_statistik_webalizer_hist_data', $data);
            return true;
        } else {
            $data = Self::processDataBody($data_body);
            $isDataRelevant = Analytics::isDateNewer($data, $isOptionSet);
            if ($isDataRelevant) {
                if (count($isOptionSet) <= 23) {
                    array_push($isOptionSet, $data[count($data) - 2]);
                    update_option('rrze_statistik_webalizer_hist_data', $isOptionSet);
                } else {
                    array_shift($isOptionSet);
                    array_push($isOptionSet, $data[count($data) - 2]);
                    update_option('rrze_statistik_webalizer_hist_data', $isOptionSet);
                }
            } else {
            }
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
            $data = substr($data_body, 0, 5000);
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
        /*if(strlen($cachable_body) !== 0){
            set_transient('rrze_statistik_webalizer_hist', $cachable_body, 120);
        }*/
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

        $output = [];
        foreach ($array as $value) {
            $array_splitted = preg_split("/	|( 	)/", $value);
            \array_splice($array_splitted, 1, -1);

            if (
                strpos($array_splitted[1], "wp-includes")
                or strpos($array_splitted[1], "wp-content")
                or strpos($array_splitted[1], "feed")
                or strpos($array_splitted[1], "robots")
                or strpos($array_splitted[1], "wp-admin")
                or strpos($array_splitted[1], '.pdf')
            ) {
            } elseif (
                strpos($array_splitted[1], ".jpg")
                or strpos($array_splitted[1], ".jpeg")
                or strpos($array_splitted[1], ".gif")
                or strpos($array_splitted[1], ".png")
                or strpos($array_splitted[1], ".svg")
            ) {
                array_push($image_files, $array_splitted);
            } else {
                array_push($sites, $array_splitted);
            }
        }
        $output = array_merge(array_slice($sites, 0, 10), array_slice($image_files, 0, 10));
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
        $data = get_option('rrze_statistik_webalizer_hist_data');

        if ($data === false) {
            Transfer::sendToJs('undefined', 'undefined', 'undefined', 'undefined', 'undefined');
            return false;
        } else {
            Transfer::sendToJs($data, Language::getAbscissa(), Language::getLanguagePackage());
            return true;
        }
    }
}
