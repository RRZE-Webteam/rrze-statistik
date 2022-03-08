<?php

namespace RRZE\Statistik;

defined('ABSPATH') || exit;

class Dashboard
{
    public function __construct()
    {
        add_action('wp_dashboard_setup', [$this, 'add_rrze_statistik_dashboard_widget']);
    }

    /**
     * Adds the Dashboard Widget
     *
     * @return void
     */
    public function add_rrze_statistik_dashboard_widget()
    {
        wp_add_dashboard_widget('rrze_statistik_widget', __('Site visitors (last 24 months)', 'rrze-statistik'), [$this, 'load_rrze_statistik_dashboard_content']);
    }

    /**
     * Defines Content of Dashboard Widget
     *
     * @return void
     */
    function load_rrze_statistik_dashboard_content()
    {
        $analytics = new Analytics();
        echo ($analytics->getLinechart('visits'));
    }
}
