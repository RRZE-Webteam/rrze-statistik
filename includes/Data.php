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
        $data = wp_remote_get('https://statistiken.rrze.fau.de/webauftritte/logs/www.wordpress.rrze.fau.de/webalizer.hist');
        var_dump($data);
    }
}