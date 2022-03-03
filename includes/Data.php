<?php

namespace RRZE\Statistik;

defined('ABSPATH') || exit;

/**
 * Collects data from statistiken.rrze.fau.de
 */
class Data
{
    public static function updateData()
    {
        // Get the data from the API.
        $url = Analytics::retrieveSiteUrl(true);
        $data_body = Self::fetchDataBody($url);
        $validation = Self::validateData($data_body);

        if ($validation === false) {
            return 'forbidden';
        } else {
            $data = Self::processDataBody($data_body);
            update_option('rrze_statistik_webalizer_hist_data', $data);
            return $data;
        }
    }

    public static function fetchDataBody($url)
    {
        $cachable = wp_remote_get(esc_url_raw($url));
        $cachable_body = wp_remote_retrieve_body($cachable);
        /*if(strlen($cachable_body) !== 0){
            set_transient('rrze_statistik_webalizer_hist', $cachable_body, 120);
        }*/
        return $cachable_body;
    }

    public static function validateData($data_body)
    {
        if (strpos($data_body, "Forbidden") !== false) {
            wp_localize_script('index-js', 'ready_check', 'forbidden');
            return false;
        } else if (strlen($data_body) === 0) {
            wp_localize_script('index-js', 'ready_check', 'forbidden');
            return false;
        } else if (strpos($data_body, "could not be found on this server") !== false) {
            wp_localize_script('index-js', 'ready_check', 'forbidden');
            return false;
        } else {
            return true;
        }
    }

    public static function processDataBody($data_body)
    {
        /* DATA STRUCTURE */
        $keymap = array(
            'monat',
            'jahr',
            'hits',
            'files',
            'hosts',
            'kbytes',
            'anzahl_monate',
            'aufgezeichnete_tage',
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

    public static function sendToJs($data_body)
    {
        $reshuffled_data = array(
            'l10n_print_after' => 'linechartDataset = ' . json_encode($data_body) . ';'
        );
        wp_localize_script('index-js', 'linechartDataset', $reshuffled_data);
        var_dump($reshuffled_data);
        return $data_body;
    }

    public static function fetchLast24Months($url)
    {
        $data = get_option('rrze_statistik_webalizer_hist_data');
        if ($data === false) {
            return 'forbidden';
        } else {
            Self::sendToJs($data);
            return $data;
        }
    }
}
