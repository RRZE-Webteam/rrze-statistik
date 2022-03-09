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
        $raw_date = date("Ym");
        $new_date = date("Ym", strtotime ( '-1 month' , strtotime ( $raw_date ) )) ;
        var_dump($new_date);
        return $new_date;
    }

    public static function retrieveSiteUrl($debug, $type)
    {
        if ($debug === false) {
            $remove_char = ["https://", "http://", "/"];
            $url = 'www.' . str_replace($remove_char, "", get_site_url());
        } else {
            $url = "www.nat.fau.de";
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

    public static function getUrlDatasetTable()
    {
        $data = get_option('rrze_statistik_url_datset');
        $data_chunks = array_chunk($data, 10);
        var_dump($data_chunks);
        $top_url = $data_chunks[0];
        $top_images = $data_chunks[1];
        var_dump($top_images);
    }
}