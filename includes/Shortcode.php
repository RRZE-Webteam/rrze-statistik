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

    public function script_that_requires_jquery() {
        wp_enqueue_script( 'script-with-dependency', plugins_url('/assets/js/highcharts/9.3.3/highcharts.js', $this->plugin_basename), array(''), false, false );
    }

    public function onLoaded()
    {
        add_shortcode('rrze_statistik', [$this, 'shortcodeOutput'], 10, 2);
    }

    public function shortcodeOutput($atts)
    {
        add_action( 'wp_enqueue_scripts', 'script_that_requires_jquery' );
        var_dump(wp_script_is( 'script-with-dependency', 'queue' ));
        return  'Weshalb funktioniert das Einbinden des JS-Files nicht';
    }

    public function getContent()
    {
        return '<p>Testoutput RRZE Statistik</p>';
    }
}