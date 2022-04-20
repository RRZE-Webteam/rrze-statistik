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

        //Just for test purposes add a new Dashboard Widget and Ajax-Action Hooks
        add_action('wp_dashboard_setup', [$this, 'prefix_add_dashboard_widget']);
        add_action('wp_ajax_widgetsave', [$this, 'misha_save_widget']); // wp_ajax_{ACTION}
        add_action('wp_ajax_showform', [$this, 'misha_ajax_show_form']); // wp_ajax_{ACTION}
    }

    /**
     * Adds the Dashboard Widget
     *
     * @return void
     */
    public function add_rrze_statistik_dashboard_widget()
    {
        $option = get_option('rrze_statistik_widget');
        if (empty($option) || $option['data_type'] === 'display_all') {
            wp_add_dashboard_widget('rrze_statistik_widget_visits', __('Site visitors over time', 'rrze-statistik'), [$this, 'load_rrze_statistik_dashboard_visits'], [$this, 'control_statistik_widgets']);
            wp_add_dashboard_widget('rrze_statistik_widget_hits', __('Hits over time', 'rrze-statistik'), [$this, 'load_rrze_statistik_dashboard_hits'], [$this, 'control_statistik_widgets']);
            wp_add_dashboard_widget('rrze_statistik_widget_hosts', __('Hosts over time', 'rrze-statistik'), [$this, 'load_rrze_statistik_dashboard_hosts'], [$this, 'control_statistik_widgets']);
            wp_add_dashboard_widget('rrze_statistik_widget_files', __('Files over time', 'rrze-statistik'), [$this, 'load_rrze_statistik_dashboard_files'], [$this, 'control_statistik_widgets']);
            wp_add_dashboard_widget('rrze_statistik_widget_kbytes', __('Kbytes over time', 'rrze-statistik'), [$this, 'load_rrze_statistik_dashboard_kbytes'], [$this, 'control_statistik_widgets']);
            wp_add_dashboard_widget('rrze_statistik_widget_urls', __('Popular Sites and Files over time', 'rrze-statistik'), [$this, 'load_rrze_statistik_dashboard_urls'], [$this, 'control_statistik_widgets']);
        } elseif ($option['data_type'] === 'hits') {
            wp_add_dashboard_widget('rrze_statistik_widget_hits', __('Hits over time', 'rrze-statistik'), [$this, 'load_rrze_statistik_dashboard_hits'], [$this, 'control_statistik_widgets']);
        } else {
            wp_add_dashboard_widget('rrze_statistik_widget_' . $option['data_type'], __($option['data_type'] . ' over time', 'rrze-statistik'), [$this, 'load_rrze_statistik_dashboard_' . $option['data_type']], [$this, 'control_statistik_widgets']);
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

        if (empty($options)) {
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
                        </option>
                    </select>
                </td>
            </tr>
        </table>
    <?php
    }

    //add misha's dashboard code 1:1
    function prefix_add_dashboard_widget()
    {
        wp_add_dashboard_widget(
            'misha_dashboard_widget', // widget ID
            'Custom Dashboard Widget', // widget title
            [$this, 'misha_dashboard_widget'], // callback #1 to display it
            [$this, 'misha_process_my_dashboard_widget'] // callback #2 for settings
        );
    }
    /*
     * Callback #1 function
     * Displays widget content
     */
    function misha_dashboard_widget()
    {

        // if the widget is configured and the post is exists
        if ($post = get_post(get_option('custom_post'))) {
            $c = do_shortcode(html_entity_decode($post->post_content));
            echo "<h2>{$post->post_title}</h2><p>{$c}</p>";
        } else {
            echo 'Widget is not configured.';
        }
    }

    /*
     * Callback #2 function
     * This function displays your widget settings
     */
    function misha_process_my_dashboard_widget() {
    
        // basic checks and save the widget settings here
        if( 'POST' == $_SERVER['REQUEST_METHOD'] 
        && isset( $_POST['my_custom_post'] ) ) {
            update_option( 'custom_post', absint($_POST['my_custom_post']) );
        }
    
        echo '<h3>Select a page that will be displayed in this widget</h3>'
        . wp_dropdown_pages( array(
            'post_type' => 'page',
            'selected' => get_option( 'custom_post' ),
            'name' => 'my_custom_post',
            'id' => 'my_custom_post',
            'show_option_none' => '- Select -',
            'echo' => false
        ) );
    
    }

    //add ajax relevant code from step 2 with adjustments:
    /*
    * This action hook shows settings form
    */
    function misha_ajax_show_form()
    {

        // widget ID should match but it is not required
        $widget_id = 'misha_dashboard_widget';
        if (!empty($_POST['rrze_statistik_widget'])) {
            $control_list = array(
                'display_type' => @$_POST['rrze_statistik_widget']['display_type'],
                'data_type' => @$_POST['rrze_statistik_widget']['data_type'],
            );

            update_option('rrze_statistik_widget', $control_list);
        }



        $options = get_option('rrze_statistik_widget');

        if (empty($options)) {
            $options['display_type'] = 'spline';
            $options['data_type'] = 'display_all';

            update_option('rrze_statistik_widget', $options);
        }
        ?>
            <form method="post" id="misha_widget_settings">
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
                                </option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="hidden" name="action" value="widgetsave" /><input type="hidden" name="widget_id" value="<?php echo $widget_id; ?>">
                            <?php wp_nonce_field('edit-dashboard-widget_' . $widget_id, 'dashboard-widget-nonce', true, false); ?>
                            <p class="submit"><input type="submit" name="submit" id="submit" style="display:inline-block" class="button button-primary" value="Submit"></p>
                        </td>
                    </tr>
                </table>
            </form>
    <?php

        die;
    }

    /*
    * This action hook saves the settings and displays the widget content
    */
    function misha_save_widget()
    {
        // security check
        Helper::debug($_POST);
        check_ajax_referer('edit-dashboard-widget_' . $_POST['widget_id'], 'dashboard-widget-nonce');

        $post_id = $_POST['rrze_statistik_widget'];

        update_option('rrze_statistik_widget', $post_id);
        Helper::debug(get_option('custom_post'));

        if (!empty(get_option('rrze_statistik_widget'))) {
            echo 'Hier kommt der output nach update der Optionen';
        } else {
            echo 'Widget is not configured.';
        }

        die;
    }
}
