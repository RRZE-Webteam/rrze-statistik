<?php

namespace RRZE\statistik;

defined('ABSPATH') || exit;

/**
 * Collects data from statistiken.rrze.fau.de
 */
class Data
{
    public function __construct($plugin_basename)
    {

        $this->plugin_basename = $plugin_basename;
    }

    public function fetchData($url)
    {
        return wp_remote_get(esc_url_raw($url));
    }

    public function retrieveBody($url)
    {
        $output = wp_remote_retrieve_body($this->fetchData($url));
        return $output;
    }

    public function fetchLast24Months($url)
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
        
        /* Wenn nicht im Uninetz wird "forbidden" returnt */

        $data = $this->fetchData($url);
        $data_body = $this->retrieveBody($url);
        if (!str_contains($data_body, "Forbidden")) {
            var_dump($data);
            $data_trim = rtrim($data_body, " \n\r\t\v");
            $array = preg_split("/\r\n|\n|\r/", $data_trim);
            $output = [];
            foreach ($array as $value) {
                array_push($output, array_combine($keymap, preg_split("/ /", $value)));
            }
            var_dump($output);

            wp_localize_script('index-js', 'linechart_dataset', $output);
        } else {
            return "Sie befinden sich aktuell nicht im Universitäts-Netzwerk.";
        }
    }
}
