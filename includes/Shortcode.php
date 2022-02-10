<?php

namespace RRZE\statistik;

defined('ABSPATH') || exit;

/**
 * Shortcode
 */
class Shortcode
{
    public $plugin_basename;

    public function __construct($plugin_basename)
    {
        $this->plugin_basename = $plugin_basename;
    }

    function script_that_requires_jquery() {
        wp_register_script( 'script-with-dependency', plugins_url('/assets/js/highcharts/9.3.3/highcharts.js', $this->plugin_basename), array('jquery'), '9.3.3', true );
        wp_enqueue_script( 'script-with-dependency' );
    }

    public function onLoaded()
    {
        add_shortcode('rrze_statistik', [$this, 'shortcodeOutput'], 10, 2);
    }

    public function shortcodeOutput($atts)
    {
        var_dump(plugins_url('/assets/js/highcharts/9.3.3/highcharts.js', $this->plugin_basename));
        return  'Weshalb funktioniert das Einbinden des JS-Files nicht';
    }

    public function getContent()
    {
        return '<p>Testoutput RRZE Statistik</p>';
    }
}