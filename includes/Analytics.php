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

    public static function getDate()
    {
        $raw_date = date("Ym");
        $new_date = date("Ym", strtotime('-1 month', strtotime($raw_date)));
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

        if ($type === 'webalizer.hist') {
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
            return printf(__('It might take a few days until personal statistics for your website ( %1$s ) are displayed within your dashboard.', 'rrze-statistik'), $site) . '</strong><br />';
        } else {
            return $this->highcharts->lineplot($container);
        };
    }

    public static function getTwoDimensionalHtmlTable($array, $array_key_1, $array_key_2, $head_desc_1, $head_desc_2){

        $html = "<div class='rrze-statistik-table'><table><tr><th>" . $head_desc_1 . '</th><th>' . $head_desc_2 . '</th></tr>';

        foreach ($array as $value) {
            $html .= "<tr><td>" . $value[$array_key_1] . '</td><td><a href="'.$value[1].'">' . htmlspecialchars($value[$array_key_2]) . '</a></td></tr>';
        }
        $html .= "</table></div>";
        return $html;
    }

    public static function getUrlDatasetTable()
    {
        $data = get_option('rrze_statistik_url_datset');
        $data_chunks = array_chunk($data, 10);
        $top_url = $data_chunks[0];
        $top_images = $data_chunks[1];

        $table1 = Self::getTwoDimensionalHtmlTable($top_url, 0, 1, __('Hits', 'rrze-statistik'), __('Sites', 'rrze-statistik'));
        $table2 = Self::getTwoDimensionalHtmlTable($top_images, 0, 1, __('Hits', 'rrze-statistik'), __('Images', 'rrze-statistik'));

        return $table1.$table2;
    }

    public static function isDateNewer($array, $arrayRef){
        $offset = count($array) - 2;
        $date = $array[$offset]['year'] . sprintf("%02d", $array[$offset]['month']);
        $dateRef = $arrayRef[count($arrayRef)-1]['year'] . sprintf("%02d", $arrayRef[count($arrayRef)-1]['month']);
        if((int)$date > (int)$dateRef){
            return true;
        } else {
            return false;
        }
    }
}
