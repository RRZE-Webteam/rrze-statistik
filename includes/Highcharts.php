<?php

namespace RRZE\statistik;

defined('ABSPATH') || exit;

class Highcharts
{
    public function __construct($plugin_basename)
    {
        $this->plugin_basename = $plugin_basename;
    }

    public function statistik_enqueue_script() {
        wp_enqueue_script( 'highcharts-js', plugins_url('/assets/js/highcharts/9.3.3/highcharts.js', $this->plugin_basename), array('jquery'), false, true );

        $modules = ['accessibility', 'data', 'export-data', 'exporting'];
        foreach ($modules as $val){
            wp_enqueue_script( 'highcharts-module-'.$val, plugins_url('/assets/js/highcharts/9.3.3/modules/'.$val.'.js', $this->plugin_basename), array('jquery'), false, true );
        }
    }

    public function loadHighcharts(){
        add_action( 'wp_enqueue_scripts', array( $this, 'statistik_enqueue_script' ));
    }
}