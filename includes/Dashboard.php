<?php

namespace RRZE\Statistik;

defined('ABSPATH') || exit;

class Dashboard
{
    public function __construct()
    {
        add_action('wp_dashboard_setup', [$this, 'my_custom_dashboard_widgets']);
    }

    public function my_custom_dashboard_widgets()
    {
        global $wp_meta_boxes;

        wp_add_dashboard_widget('custom_help_widget', __('Site visitors (last 24 months)', 'rrze-statistik'), [$this, 'custom_dashboard_help'], 'column3');
    }

    function custom_dashboard_help()
    {
        $analytics = new Analytics();
        echo ($analytics->getLinechart(get_site_url()));
        
        
    }
}
