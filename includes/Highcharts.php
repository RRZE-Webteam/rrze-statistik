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
        wp_enqueue_script(
            'index-js',
            plugins_url('dist/highchartsIndex.js', plugin()->getBasename()),
            array(),
            plugin()->getVersion(),
            true
        );
    }

    public function loadHighcharts()
    {
        add_action('admin_enqueue_scripts', array($this, 'statistik_enqueue_script'));
    }

    /**
     * Creates HTML for highcharts container if data is legit
     *
     * @param string $container
     * @return string
     */
    public function lineplot($container)
    {
        $data = get_transient('rrze_statistik_data_webalizer_hist');

        if ($data === false) {
            $output = "No data points available.";
            return $output;
        } else {
            $logs_url = Analytics::retrieveSiteUrl('logs');
            return
            '<figure class="rrze-statistik highcharts-figure">
            <div id="' . $container . '"></div>
            <p class="highcharts-description">' . Language::getLinechartDescription($container) . '</p><hr />
            <a href=' . $logs_url . '>' . __('Source: www.statistiken.rrze.fau.de', 'rrze-statistik') . '</a><hr />
            </figure>
            <button id="' . $container . '-getcsv">' . Language::getCSVButtonText() . '</button>';
        }
    }
}
