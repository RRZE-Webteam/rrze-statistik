<?php

namespace RRZE\Statistik;

defined('ABSPATH') || exit;

/**
 * Loads Highcharts js-files and returns the needed html structure for js-files to work
 */
class Highcharts
{
    public function __construct()
    {
        $this->loadHighcharts();
    }

    public function statistik_enqueue_script()
    {
        wp_enqueue_style(
            'highcharts-css', 
            plugins_url('dist/highcharts.css', plugin()->getBasename()),
            array(), 
            plugin()->getVersion(), 
            'all'
        );
        wp_enqueue_script(
            'highcharts-js', 
            plugins_url('assets/js/highcharts/9.3.3/highcharts.js', plugin()->getBasename()),
            array(), 
            plugin()->getVersion(),
            true
        );
        wp_enqueue_script(
            'index-js', 
            plugins_url('dist/highchartsIndex.js', plugin()->getBasename()),
            array(), 
            plugin()->getVersion(),
            true
        );

        $modules = ['accessibility', 'data', 'export-data', 'exporting'];
        foreach ($modules as $val) {
            wp_enqueue_script(
                'highcharts-module-' . $val, 
                plugins_url('assets/js/highcharts/9.3.3/modules/' . $val . '.js', plugin()->getBasename()),
                array(), 
                plugin()->getVersion(), 
                true
            );
        }

        $maps = ['accessibility', 'data', 'export-data', 'exporting'];
        foreach ($maps as $val) {
            wp_enqueue_script(
                'highcharts-module-' . $val, 
                plugins_url('assets/js/highcharts/9.3.3/modules/' . $val . '.js.map', plugin()->getBasename()),
                array(), 
                plugin()->getVersion(),
                true
            );
        }
    }

    public function loadHighcharts()
    {
        add_action('wp_enqueue_scripts', array($this, 'statistik_enqueue_script'));
        add_action('admin_enqueue_scripts', array($this, 'statistik_enqueue_script'));
;
    }

    public function lineplot()
    {
        $data = get_option('rrze_statistik_webalizer_hist_data');
        
        if ($data === false) {
            $output = "No data points available.";
        } else {
           //
        }

        return  '<div class="rrze-statistik"><figure class="highcharts-figure">
        <div id="container"></div>
        <p class="highcharts-description">'."Hier Datentabelle einfügen".'
        </p>
      </figure></div>';
    }
}
