<?php

namespace RRZE\Statistik;

defined('ABSPATH') || exit;

/**
 * Collects and processes data
 */
class Data
{
    /**
     * Function for Cron Job. Returns true if successful
     *
     * @return boolen
     */
    public static function updateData()
    {
        // Get the data from the API.
        $url = Analytics::retrieveSiteUrl(true);
        $data_body = Self::fetchDataBody($url);
        $validation = Self::validateData($data_body);

        if ($validation === false) {
            return false;
        } else {
            $data = Self::processDataBody($data_body);
            update_option('rrze_statistik_webalizer_hist_data', $data);
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
     * Evaluates if fetch was successful
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
