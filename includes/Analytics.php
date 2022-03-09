<?php

namespace RRZE\Statistik;

defined('ABSPATH') || exit;

/**
 * Pulls processed data & initiates Highchart plots
 */
class Analytics
{
    public function __construct()
    {
        $this->highcharts = new Highcharts();
    }

    public function getImgLink($type)
    {
        if ($type === 'forbidden') {
            return plugin_dir_url(__DIR__) . 'assets/img/no_data.svg';
        }
    }

    public static function getDate(){
        $date = date("Ym");
        if(substr($date, -2) === '01'){
            $year = (int)(substr($date, 0, -2))-1;
            $date = (string)$year . '12';
        }
        return $date;
    }

    public static function retrieveSiteUrl($debug, $type)
    {
        if ($debug === false) {
            $remove_char = ["https://", "http://", "/"];
            $url = 'www.' . str_replace($remove_char, "", get_site_url());
        } else {
            $url = "www.fau.de";
        }

        if ($type === 'webalizer.hist'){
            $output = 'https://statistiken.rrze.fau.de/webauftritte/logs/' . $url . '/webalizer.hist';
        } else {
            $output = 'https://statistiken.rrze.fau.de/webauftritte/logs/' . $url . '/url_' . Self::getDate() . '.tab';
        }
        return $output;
    }

    public function getLinechart($container)
    {
        //set value of $url to true while debugging..
        //$url = $this->retrieveSiteUrl(true);
        $remove_char = ["https://", "http://", "/"];
        $site = 'www.' . str_replace($remove_char, "", get_site_url());
        $ready_check = Data::processLinechartDataset(Self::retrieveSiteUrl(true, 'webalizer.hist'));
        if ($ready_check === false) {
            return '<img src="' . $this->getImgLink('forbidden') . '" alt=""><br /><strong>'.printf(__('It might take a few days until personal statistics for your website ( %1$s ) are displayed within your dashboard.', 'rrze-statistik'), $site).'</strong><br />';
        } else {
            return $this->highcharts->lineplot($container);
        };
    }
}