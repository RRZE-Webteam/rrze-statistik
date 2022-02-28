<?php

namespace RRZE\Statistik;

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

    public function getImgLink($type)
    {
        if ($type === 'forbidden') {
            return plugin_dir_url(__DIR__) . 'assets/img/no_connection.svg';
        } else {
            return plugin_dir_url(__DIR__) . 'assets/img/no_data.svg';
        }
    }

    public function retrieveSiteUrl($debug)
    {
        if ($debug === false) {
            $remove_char = ["https://", "http://", "/"];
            return 'www.' . str_replace($remove_char, "", get_site_url());
        } else {
            return "www.fau.de";
        }
    }

    public function getLinechart()
    {
        //set value of $url to true while debugging..
        $url = $this->retrieveSiteUrl(false);
        $data = new Data($this->plugin_basename);
        $ready_check = $data->fetchLast24Months('https://statistiken.rrze.fau.de/webauftritte/logs/' . $url . '/webalizer.hist');
        if ($ready_check === 'forbidden') {
            return '<img src="' . $this->getImgLink('forbidden') . '" alt=""><strong>Sie sind aktuell nicht mit dem Universitätsnetzwerk verbunden.</strong><br/>Verbinden Sie sich via VPN, um auf die letzten Statistiken zuzugreifen.';
        } else if ($ready_check === 'no_data') {
            return '<img src="' . $this->getImgLink('') . '" alt=""><strong>Ihre Seite konnte nicht auf statistiken.rrze.fau.de gefunden werden.</strong><br/> Falls Ihre Seite (' . $url . ') neu ist, kann es einen Monat dauern, bevor Statistiken dargestellt werden können.';
        } else {
            return $this->highcharts->lineplot();
        };
    }

    public function retrievePopularSites()
    {
        $url = $this->retrieveSiteURL(true);
        $data = new Data($this->plugin_basename);
        $date = date("Ym");
        $ready_check = $data->fetchPopularUrls('https://statistiken.rrze.fau.de/webauftritte/logs/' . $url . '/url_202201.tab');
    }
}
