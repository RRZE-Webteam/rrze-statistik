<?php

namespace RRZE\statistik;

defined('ABSPATH') || exit;

/**
 * Loads Highcharts js-files and returns the needed html structure for js-files to work
 */
class Highcharts
{
    public function __construct($plugin_basename)
    {
        $this->plugin_basename = $plugin_basename;
        $this->loadHighcharts();
    }

    public function statistik_enqueue_script()
    {
        wp_enqueue_style('highcharts-css', plugins_url('/assets/css/highcharts-style.css', $this->plugin_basename), array(), false, 'all');
        wp_enqueue_script('highcharts-js', plugins_url('/assets/js/highcharts/9.3.3/highcharts.js', $this->plugin_basename), array('jquery'), false, true);
        wp_enqueue_script('index-js', plugins_url('/assets/js/index.js', $this->plugin_basename), array('jquery'), false, true);

        $modules = ['accessibility', 'data', 'export-data', 'exporting'];
        foreach ($modules as $val) {
            wp_enqueue_script('highcharts-module-' . $val, plugins_url('/assets/js/highcharts/9.3.3/modules/' . $val . '.js', $this->plugin_basename), array('jquery'), false, true);
        }
    }

    public function loadHighcharts()
    {
        add_action('wp_enqueue_scripts', array($this, 'statistik_enqueue_script'));
    }

    public function lineplot()
    {
        return  '<figure class="highcharts-figure">
        <div id="container"></div>
        <p class="highcharts-description">
          Die Besuche der letzten 24 Monate.
        </p>
      </figure>';
    }
}
