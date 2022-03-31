<?php

namespace RRZE\Statistik;

defined('ABSPATH') || exit;

/**
 * Inititalizes the dashboard plugins and their basic behavior
 */
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
        wp_add_dashboard_widget('rrze_statistik_widget_visits', __('Site visitors over time', 'rrze-statistik'), [$this, 'load_rrze_statistik_dashboard_visits']);
        wp_add_dashboard_widget('rrze_statistik_widget_hits', __('Hits over time', 'rrze-statistik'), [$this, 'load_rrze_statistik_dashboard_hits']);
        wp_add_dashboard_widget('rrze_statistik_widget_hosts', __('Hosts over time', 'rrze-statistik'), [$this, 'load_rrze_statistik_dashboard_hosts']);
        wp_add_dashboard_widget('rrze_statistik_widget_files', __('Files over time', 'rrze-statistik'), [$this, 'load_rrze_statistik_dashboard_files']);
        wp_add_dashboard_widget('rrze_statistik_widget_kbytes', __('Kbytes over time', 'rrze-statistik'), [$this, 'load_rrze_statistik_dashboard_kbytes']);
        wp_add_dashboard_widget('rrze_statistik_widget_urls', __('Popular Sites and Files over time', 'rrze-statistik'), [$this, 'load_rrze_statistik_dashboard_urls']);
    }

    /**
     * Defines Content of Dashboard Widget
     *
     * @return void
     */
    function load_rrze_statistik_dashboard_visits()
    {
        $analytics = new Analytics();
        echo ($analytics->getLinechart('visits'));
    }

    function load_rrze_statistik_dashboard_hits()
    {
        $analytics = new Analytics();
        echo ($analytics->getLinechart('hits'));
    }

    function load_rrze_statistik_dashboard_hosts()
    {
        $analytics = new Analytics();
        echo ($analytics->getLinechart('hosts'));
    }

    function load_rrze_statistik_dashboard_files()
    {
        $analytics = new Analytics();
        echo ($analytics->getLinechart('files'));
    }

    function load_rrze_statistik_dashboard_kbytes()
    {
        $analytics = new Analytics();
        echo ($analytics->getLinechart('kbytes'));
    }

    function load_rrze_statistik_dashboard_urls()
    {
        echo (Analytics::getUrlDatasetTable());
    }
}
