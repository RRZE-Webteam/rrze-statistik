<?php

namespace RRZE\Statistik;

defined('ABSPATH') || exit;

/**
 * Combines raw data & Highcharts plots
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

    public static function retrieveSiteUrl($debug)
    {
        if ($debug === false) {
            $remove_char = ["https://", "http://", "/"];
            $url = 'www.' . str_replace($remove_char, "", get_site_url());
        } else {
            $url = "www.fau.de";
        }
        return 'https://statistiken.rrze.fau.de/webauftritte/logs/' . $url . '/webalizer.hist';
    }

    public function getLinechart()
    {
        //set value of $url to true while debugging..
        $url = $this->retrieveSiteUrl(true);
        $remove_char = ["https://", "http://", "/"];
        $site = 'www.' . str_replace($remove_char, "", get_site_url());
        $ready_check = Data::fetchLast24Months(Self::retrieveSiteUrl(true));
        if ($ready_check === 'forbidden') {
            return '<img src="' . $this->getImgLink('forbidden') . '" alt=""><strong>'.__('Es kann einige Tage dauern, bevor die Statistiken zu Ihrer Webseite (', 'rrze-statistik') . $site . __(') im Dashboard dargestellt werden.', 'rrze-statistik').'</strong><br/>';
        } else {
            return $this->highcharts->lineplot();
        };
    }
}
