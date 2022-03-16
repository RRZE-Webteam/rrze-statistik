<?php

namespace RRZE\Statistik;

defined('ABSPATH') || exit;

class Main
{
    /**
     * Variablen Werte zuweisen.
     * @param string $pluginFile Path and file name of plugin
     */
    public function __construct()
    {
        new Helper();
        $highcharts = new Highcharts();
        $highcharts->loadHighcharts();

        new Dashboard();

        $shortcode = new Shortcode();
        $shortcode->onLoaded();

        Cron::init();
    }
}
