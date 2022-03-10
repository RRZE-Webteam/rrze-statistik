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

        $modules = ['accessibility', 'data', 'export-data', 'exporting', 'high-contrast-light', 'series-label'];
        foreach ($modules as $val) {
            wp_enqueue_script(
                'highcharts-module-' . $val, 
                plugins_url('assets/js/highcharts/9.3.3/modules/' . $val . '.js', plugin()->getBasename()),
                array(), 
                plugin()->getVersion(), 
                true
            );
        }

        $maps = ['accessibility', 'data', 'export-data', 'exporting', 'high-contrast-light', 'series-label'];
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

    public function lineplot($container)
    {
        $data = get_option('rrze_statistik_webalizer_hist_data');
        
        if ($data === false) {
            $output = "No data points available.";
            return $output;
        } else {
            return
            '<figure class="rrze-statistik highcharts-figure">
            <div id="'.$container.'"></div>
            <p class="highcharts-description">Line chart demonstrating some accessibility features of Highcharts. The chart displays the most commonly used screen readers in surveys taken by WebAIM from December 2010 to September 2019. JAWS was the most used screen reader until 2019, when NVDA took over. VoiceOver is the third most used screen reader, followed by Narrator. ZoomText/Fusion had a surge in 2015, but usage is otherwise low. The overall use of other screen readers has declined drastically the past few years.</p>
            </figure>';
        }


    }
}
