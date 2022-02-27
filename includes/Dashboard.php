<?php

namespace RRZE\statistik;

defined('ABSPATH') || exit;

class Dashboard
{
    public function __construct($plugin_basename){
        $this->plugin_basename = $plugin_basename;
        add_action('wp_dashboard_setup', [$this, 'my_custom_dashboard_widgets']);
    }

    public function my_custom_dashboard_widgets() {
        global $wp_meta_boxes;
        
        wp_add_dashboard_widget('custom_help_widget', 'RRZE_Statistik', [$this, 'custom_dashboard_help'], 'column3');
    }
        
    function custom_dashboard_help() {
        $analytics = new Analytics($this->plugin_basename);
        echo( $analytics->getLinechart('www.fau.de') );
    }
}