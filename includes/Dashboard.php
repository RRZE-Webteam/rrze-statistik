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
        $option = get_option('rrze_statistik_widget');
        if(empty($option) || $option['data_type'] === 'display_all'){ 
            wp_add_dashboard_widget('rrze_statistik_widget_visits', __('Site visitors over time', 'rrze-statistik'), [$this, 'load_rrze_statistik_dashboard_visits'], [$this, 'control_statistik_widgets']);
            wp_add_dashboard_widget('rrze_statistik_widget_hits', __('Hits over time', 'rrze-statistik'), [$this, 'load_rrze_statistik_dashboard_hits'], [$this, 'control_statistik_widgets']);
            wp_add_dashboard_widget('rrze_statistik_widget_hosts', __('Hosts over time', 'rrze-statistik'), [$this, 'load_rrze_statistik_dashboard_hosts'], [$this, 'control_statistik_widgets']);
            wp_add_dashboard_widget('rrze_statistik_widget_files', __('Files over time', 'rrze-statistik'), [$this, 'load_rrze_statistik_dashboard_files'], [$this, 'control_statistik_widgets']);
            wp_add_dashboard_widget('rrze_statistik_widget_kbytes', __('Kbytes over time', 'rrze-statistik'), [$this, 'load_rrze_statistik_dashboard_kbytes'], [$this, 'control_statistik_widgets']);
            wp_add_dashboard_widget('rrze_statistik_widget_urls', __('Popular Sites and Files over time', 'rrze-statistik'), [$this, 'load_rrze_statistik_dashboard_urls'], [$this, 'control_statistik_widgets']);
        }
        elseif ($option['data_type'] === 'hits'){
            wp_add_dashboard_widget('rrze_statistik_widget_hits', __('Hits over time', 'rrze-statistik'), [$this, 'load_rrze_statistik_dashboard_hits'], [$this, 'control_statistik_widgets']);
        }
        else {
            wp_add_dashboard_widget('rrze_statistik_widget_'.$option['data_type'], __($option['data_type']. ' over time', 'rrze-statistik'), [$this, 'load_rrze_statistik_dashboard_'.$option['data_type']], [$this, 'control_statistik_widgets']);
        }
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
        $source_link_target = Analytics::retrieveSiteURL('');
        $source_link_target = substr($source_link_target, 0, -4);

        if (!empty($source_link_target)) {
            echo ('<p><a href="' . $source_link_target . '.html' . '" target="_blank">' . __('Source: Statistiken.rrze.fau.de', 'rrze-statistik') . '</a></p>');
        }
    }

    function control_statistik_widgets(){

        if (!empty($_POST['rrze_statistik_widget'])) {
            $control_list = array(
                'display_type' => @$_POST['rrze_statistik_widget']['display_type'],
                'data_type' => @$_POST['rrze_statistik_widget']['data_type'],
            );

            update_option('rrze_statistik_widget', $control_list);
        }

    

        $options = get_option('rrze_statistik_widget');

        if(empty($options)) {
            $options['display_type'] = 'spline';
            $options['data_type'] = 'display_all';

            update_option('rrze_statistik_widget', $options);
        }

        Helper::debug($options);
        
        ?>
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Display type', 'rrze-statistik'); ?></th>
                <td>
                    <select name="rrze_statistik_widget[display_type]">
                        <option value="spline" <?php selected($options['display_type'], 'spline'); ?>><?php _e('Spline', 'rrze-statistik'); ?></option>
                        <option value="areaspline" <?php selected($options['display_type'], 'areaspline'); ?>><?php _e('Area-spline', 'rrze-statistik'); ?></option>
                        <option value="column" <?php selected($options['display_type'], 'column'); ?>><?php _e('Column', 'rrze-statistik'); ?></option>
                        <option value="bar" <?php selected($options['display_type'], 'bar'); ?>><?php _e('Bar', 'rrze-statistik'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Data type', 'rrze-statistik'); ?></th>
                <td>
                    <select name="rrze_statistik_widget[data_type]">
                        <option value="display_all" <?php selected($options['data_type'], 'display_all'); ?>><?php _e('Display all', 'rrze-statistik'); ?></option>
                        <option value="visits" <?php selected($options['data_type'], 'visits'); ?>><?php _e('Visits', 'rrze-statistik'); ?></option>
                        <option value="hits" <?php selected($options['data_type'], 'hits'); ?>><?php _e('Hits', 'rrze-statistik'); ?></option>
                        <option value="hosts" <?php selected($options['data_type'], 'hosts'); ?>><?php _e('Hosts', 'rrze-statistik'); ?></option>
                        <option value="files" <?php selected($options['data_type'], 'files'); ?>><?php _e('Files', 'rrze-statistik'); ?></option>
                        <option value="kbytes" <?php selected($options['data_type'], 'kbytes'); ?>><?php _e('Kbytes', 'rrze-statistik'); ?></option>
                        <option value="urls" <?php selected($options['data_type'], 'urls'); ?>><?php _e('Popular Sites and Files', 'rrze-statistik'); ?>
        </table>
        <?php
    }
}
