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

    public function fetchLast24Months()
    {
        $data = wp_remote_get( esc_url_raw('https://statistiken.rrze.fau.de/webauftritte/logs/www.wordpress.rrze.fau.de/webalizer.hist'));
        $data_body = wp_remote_retrieve_body($data);
        $data_trim = rtrim($data_body," \n\r\t\v");
        //$replaced = str_replace(" ", ",", $data_body);
        //$output = preg_split("/ /", $data_body);
        $array = preg_split("/\r\n|\n|\r/", $data_trim);
        $output = [];
        foreach($array as $value){
            array_push($output, preg_split("/ /", $value));
        }
        var_dump($output);
    } 
}