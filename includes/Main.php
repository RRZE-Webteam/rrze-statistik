<?php

namespace RRZE\statistik;

defined('ABSPATH') || exit;

class Main
{
    /**
     * Full path- and file name of plugin.
     * @var string
     */
    public $plugin_basename;

    /**
     * Variablen Werte zuweisen.
     * @param string $pluginFile Path and file name of plugin
     */
    public function __construct($plugin_basename)
    {
        $this->plugin_basename = $plugin_basename;
    }


    public function onLoaded()
    {
        new Helper();
        $highcharts = new Highcharts($this->plugin_basename);
        $highcharts->loadHighcharts();

        new Dashboard($this->plugin_basename);

        $shortcode = new Shortcode($this->plugin_basename);
        $shortcode->onLoaded();
    }
}
