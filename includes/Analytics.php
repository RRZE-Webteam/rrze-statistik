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
            return plugin_dir_url(__DIR__) . 'assets/img/no_connection.svg';
        } else {
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
        $ready_check = Data::fetchLast24Months();
        if ($ready_check === 'forbidden') {
            return '<img src="' . $this->getImgLink('forbidden') . '" alt=""><strong>Sie sind aktuell nicht mit dem Universitätsnetzwerk verbunden.</strong><br/>Verbinden Sie sich via VPN, um auf die letzten Statistiken zuzugreifen.';
        } else if ($ready_check === 'no_data') {
            return '<img src="' . $this->getImgLink('') . '" alt=""><strong>Ihre Seite konnte nicht auf statistiken.rrze.fau.de gefunden werden.</strong><br/> Falls Ihre Seite (' . $url . ') neu ist, kann es einen Monat dauern, bevor Statistiken dargestellt werden können.';
        } else {
            return $this->highcharts->lineplot();
        };
    }
}
