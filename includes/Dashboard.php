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
        add_action('wp_ajax_widgetsave', [$this, 'rrze_statistik_save_widget']);
        add_action('wp_ajax_showform', [$this, 'rrze_statistik_ajax_show_form']);

        Self::prefill_options();
    }

    /**
     * Checks if Options are empty and sets default values
     *
     * @return void
     */
    public static function prefill_options()
    {
        $options = get_option('rrze_statistik_widget');

        if ($options === false) {
            $options = [];
        }
        
        if (empty($options)) {
            $options['display_type'] = 'spline';
            $options['data_type'] = 'visits_and_sites';
        
            update_option('rrze_statistik_widget', $options);
        }
    }

    /**
     * Adds the Dashboard Widgets
     *
     * @return void
     */
    public function add_rrze_statistik_dashboard_widget()
    {
        $option = get_option('rrze_statistik_widget');
        switch ($option['data_type']) {
            case 'display_all':
                wp_add_dashboard_widget('rrze_statistik_widget_visits', Language::dashboardWidgetTitle('visits'), [$this, 'load_rrze_statistik_dashboard_visits'], [$this, 'control_statistik_widgets']);
                wp_add_dashboard_widget('rrze_statistik_widget_hits', Language::dashboardWidgetTitle('hits'), [$this, 'load_rrze_statistik_dashboard_hits'], [$this, 'control_statistik_widgets']);
                wp_add_dashboard_widget('rrze_statistik_widget_hosts', Language::dashboardWidgetTitle('hosts'), [$this, 'load_rrze_statistik_dashboard_hosts'], [$this, 'control_statistik_widgets']);
                wp_add_dashboard_widget('rrze_statistik_widget_files', Language::dashboardWidgetTitle('files'), [$this, 'load_rrze_statistik_dashboard_files'], [$this, 'control_statistik_widgets']);
                wp_add_dashboard_widget('rrze_statistik_widget_kbytes', Language::dashboardWidgetTitle('kbytes'), [$this, 'load_rrze_statistik_dashboard_kbytes'], [$this, 'control_statistik_widgets']);
                wp_add_dashboard_widget('rrze_statistik_widget_urls', Language::dashboardWidgetTitle('urls'), [$this, 'load_rrze_statistik_dashboard_urls'], [$this, 'control_statistik_widgets']);
                break;
            case 'visits_and_sites':
                wp_add_dashboard_widget('rrze_statistik_widget_visits', Language::dashboardWidgetTitle('visits'), [$this, 'load_rrze_statistik_dashboard_visits'], [$this, 'control_statistik_widgets']);
                wp_add_dashboard_widget('rrze_statistik_widget_urls', Language::dashboardWidgetTitle('urls'), [$this, 'load_rrze_statistik_dashboard_urls'], [$this, 'control_statistik_widgets']);
                break;
            default:
                wp_add_dashboard_widget('rrze_statistik_widget_' . $option['data_type'], Language::dashboardWidgetTitle($option['data_type']), [$this, 'load_rrze_statistik_dashboard_' . $option['data_type']], [$this, 'control_statistik_widgets']);
                break;
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

    /**
     * Static Settings for Dashboard Widget - Overwritten by Ajax functions below
     *
     * @return void
     */
    function control_statistik_widgets()
    {

        if (!empty($_POST['rrze_statistik_widget'])) {
            $control_list = array(
                'display_type' => @$_POST['rrze_statistik_widget']['display_type'],
                'data_type' => @$_POST['rrze_statistik_widget']['data_type'],
            );

            update_option('rrze_statistik_widget', $control_list);
        }



        $options = get_option('rrze_statistik_widget');

        // Helper::debug($options);

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
                        <option value="visits_and_sites" <?php selected($options['data_type'], 'visits_and_sites'); ?>><?php _e('Visits and popular sites', 'rrze-statistik'); ?></option>
                        <option value="display_all" <?php selected($options['data_type'], 'display_all'); ?>><?php _e('Display all', 'rrze-statistik'); ?></option>
                        <option value="visits" <?php selected($options['data_type'], 'visits'); ?>><?php _e('Visits', 'rrze-statistik'); ?></option>
                        <option value="hits" <?php selected($options['data_type'], 'hits'); ?>><?php _e('Hits', 'rrze-statistik'); ?></option>
                        <option value="hosts" <?php selected($options['data_type'], 'hosts'); ?>><?php _e('Hosts', 'rrze-statistik'); ?></option>
                        <option value="files" <?php selected($options['data_type'], 'files'); ?>><?php _e('Files', 'rrze-statistik'); ?></option>
                        <option value="kbytes" <?php selected($options['data_type'], 'kbytes'); ?>><?php _e('Kbytes', 'rrze-statistik'); ?></option>
                        <option value="urls" <?php selected($options['data_type'], 'urls'); ?>><?php _e('Popular sites', 'rrze-statistik'); ?>
                        </option>
                    </select>
                </td>
            </tr>
        </table>
    <?php
    }

    /**
     * Ajax Widget Settings Action. Ajax Script inside ../src/highcharts/ajax.js
     *
     * @return void
     */
    function rrze_statistik_ajax_show_form()
    {
        $widget_id = 'rrze_statistik_dashboard_widget';
        if (!empty($_POST['rrze_statistik_widget'])) {
            $control_list = array(
                'display_type' => @$_POST['rrze_statistik_widget']['display_type'],
                'data_type' => @$_POST['rrze_statistik_widget']['data_type'],
            );

            update_option('rrze_statistik_widget', $control_list);
        }

        $options = get_option('rrze_statistik_widget');

    ?>
        <form method="post" id="rrze_statistik_settings">
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Display type', 'rrze-statistik'); ?></th>
                    <td>
                        <select name="rrze_statistik_widget[display_type]">
                            <option value="spline" <?php selected($options['display_type'], 'spline'); ?>><?php _e('Line chart', 'rrze-statistik'); ?></option>
                            <option value="areaspline" <?php selected($options['display_type'], 'areaspline'); ?>><?php _e('Area chart', 'rrze-statistik'); ?></option>
                            <option value="column" <?php selected($options['display_type'], 'column'); ?>><?php _e('Column chart', 'rrze-statistik'); ?></option>
                            <option value="bar" <?php selected($options['display_type'], 'bar'); ?>><?php _e('Bar chart', 'rrze-statistik'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Data type', 'rrze-statistik'); ?></th>
                    <td>
                        <select name="rrze_statistik_widget[data_type]">
                            <option value="visits_and_sites" <?php selected($options['data_type'], 'visits_and_sites'); ?>><?php _e('Visits and popular sites', 'rrze-statistik'); ?></option>
                            <option value="visits" <?php selected($options['data_type'], 'visits'); ?>><?php _e('Visits', 'rrze-statistik'); ?></option>
                            <option value="hits" <?php selected($options['data_type'], 'hits'); ?>><?php _e('Hits', 'rrze-statistik'); ?></option>
                            <option value="hosts" <?php selected($options['data_type'], 'hosts'); ?>><?php _e('Hosts', 'rrze-statistik'); ?></option>
                            <option value="files" <?php selected($options['data_type'], 'files'); ?>><?php _e('Files', 'rrze-statistik'); ?></option>
                            <option value="kbytes" <?php selected($options['data_type'], 'kbytes'); ?>><?php _e('Kbytes', 'rrze-statistik'); ?></option>
                            <option value="urls" <?php selected($options['data_type'], 'urls'); ?>><?php _e('Popular Sites', 'rrze-statistik'); ?></option>
                            <option value="display_all" <?php selected($options['data_type'], 'display_all'); ?>><?php _e('Display all', 'rrze-statistik'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>
                        <input type="hidden" name="action" value="widgetsave" /><input type="hidden" name="widget_id" value="<?php echo $widget_id; ?>">
                        <?php echo wp_nonce_field('edit-dashboard-widget_' . $widget_id, 'dashboard-widget-nonce', true, false); ?>
                        <p class="submit"><input type="submit" name="submit" id="submit" style="display:inline-block" class="button button-primary" value="<?php _e('submit', 'rrze-statistik'); ?>"></p>
                    </td>
                </tr>
            </table>
        </form>
<?php

        die;
    }

    /**
     * Ajax Widgetsave Action. Ajax Script inside ../src/highcharts/ajax.js
     *
     * @return void
     */
    function rrze_statistik_save_widget()
    {
        // security check
        check_ajax_referer('edit-dashboard-widget_' . $_POST['widget_id'], 'dashboard-widget-nonce');

        if (!empty($_POST['widget_id'])) {
            $updated_settings = $_POST['rrze_statistik_widget'];
            update_option('rrze_statistik_widget', $updated_settings);
        } else {
            do_action('rrze.log.info', _('No widget_id in POST-Array for RRZE Statistik Widget', 'rrze-statistik'));
        }

        if (!empty(get_option('rrze_statistik_widget'))) {
            $selector = $_POST['selector'];
            $selectorDataType = substr($selector, 15);

            $analytics = new Analytics();
            echo ($analytics->getLinechart($selectorDataType));
        } else {
            _e('No settings found.', 'rrze-statistik');
            do_action('rrze.log.info', _('No settings found for rrze-statistik dashboard display after AJAX request.', 'rrze-statistik'));
        }

        die;
    }
}
