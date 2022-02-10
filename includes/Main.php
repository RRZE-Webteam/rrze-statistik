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

    public function statistik_enqueue_script() {
        wp_enqueue_script( 'script-with-dependency', plugins_url('/assets/js/highcharts/9.3.3/highcharts.js', $this->plugin_basename), array('jquery'), false, true );
    }

    public function onLoaded()
    {
        new Helper();

        add_action( 'wp_enqueue_scripts', array( $this, 'statistik_enqueue_script' ));
        $shortcode = new Shortcode($this->plugin_basename);
        $shortcode->onLoaded();
    }
}
