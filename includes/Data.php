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
        $data = $this->fetchData($url);
        if ($data !== null) {
            $data_body = $this->retrieveBody($url);
            $data_trim = rtrim($data_body, " \n\r\t\v");
            $array = preg_split("/\r\n|\n|\r/", $data_trim);
            $output = [];
            foreach ($array as $value) {
                array_push($output, array_combine($keymap, preg_split("/ /", $value)));
            }
            var_dump($output);
        } else {
            return "requested data could not been retrieved!";
        }
    }
}
