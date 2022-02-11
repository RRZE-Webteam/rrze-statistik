<?php

namespace RRZE\statistik;

defined('ABSPATH') || exit;

/**
 * Combines raw data & Highcharts plots
 */
class Analytics
{
    public function __construct($plugin_basename)
    {
        $this->plugin_basename = $plugin_basename;
        $this->highcharts = new Highcharts($this->plugin_basename);
    }

    /**
     * Create a Linechart
     *
     * @return void
     */
    public function getLinechart($data){
        $data = new Data($this->plugin_basename);
        $data->fetchLast24Months('https://statistiken.rrze.fau.de/webauftritte/logs/www.wordpress.rrze.fau.de/webalizer.hist');
        return $this->highcharts->lineplot();
    }
}