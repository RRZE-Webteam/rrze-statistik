<?php

namespace RRZE\Statistik;

defined('ABSPATH') || exit;

/** Registers one statistics widget and stores each user's view preferences. */
class Dashboard
{
    /** @var string[] Supported statistics tabs, including the popular-pages table. */
    const VIEWS = ['visits', 'hits', 'hosts', 'files', 'kbytes', 'urls'];

    /** @var string[] Supported chart representations. */
    const DISPLAY_TYPES = ['spline', 'areaspline', 'column', 'bar'];

    /** Registers dashboard rendering and the authenticated preference endpoint. */
    public function __construct()
    {
        add_action('wp_dashboard_setup', [$this, 'add_rrze_statistik_dashboard_widget']);
        add_action('wp_ajax_rrze_statistik_save_preferences', [$this, 'savePreferences']);
        self::prefill_options();
    }

    /**
     * Retains legacy defaults as the starting point for existing installations.
     * @return void
     */
    public static function prefill_options()
    {
        if (!get_option('rrze_statistik_widget')) {
            update_option('rrze_statistik_widget', [
                'display_type' => 'spline', 'data_type' => 'visits_and_sites',
            ]);
        }
    }

    /**
     * Resolves site-specific personal preferences with validated legacy defaults.
     * Legacy combined views start on visits; all tabs remain available.
     * @return array<string,string> Selected view and chart representation.
     */
    public static function getPreferences()
    {
        $legacy = get_option('rrze_statistik_widget');
        $legacy = is_array($legacy) ? $legacy : [];
        $personal = get_user_option('rrze_statistik_preferences');
        $personal = is_array($personal) ? $personal : [];
        $preferences = [];
        foreach (['view' => self::VIEWS, 'displayType' => self::DISPLAY_TYPES] as $key => $allowed) {
            $oldKey = $key === 'view' ? 'data_type' : 'display_type';
            $fallback = in_array($legacy[$oldKey] ?? null, $allowed, true) ? $legacy[$oldKey] : $allowed[0];
            $preferences[$key] = in_array($personal[$key] ?? null, $allowed, true) ? $personal[$key] : $fallback;
        }
        return $preferences;
    }

    /**
     * Registers one widget, retaining the common visits widget's placement/visibility.
     * @return void
     */
    public function add_rrze_statistik_dashboard_widget()
    {
        wp_add_dashboard_widget(
            'rrze_statistik_widget_visits',
            __('Website statistics', 'rrze-statistik'),
            [$this, 'render']
        );
    }

    /**
     * Publishes shared monthly data once and renders all available tab panels.
     * @return void
     */
    public function render()
    {
        Data::processLinechartDataset(Analytics::retrieveSiteUrl('webalizer.hist'));
        echo Charts::dashboard();
    }

    /**
     * Saves allowlisted preferences only for the current user on the current site.
     * @return void The WordPress JSON response terminates the request.
     */
    public function savePreferences()
    {
        check_ajax_referer('rrze_statistik_preferences', 'nonce');
        if (!current_user_can('read')) {
            wp_send_json_error(null, 403);
            return;
        }
        $view = $_POST['view'] ?? null;
        $displayType = $_POST['displayType'] ?? null;
        if (!in_array($view, self::VIEWS, true) || !in_array($displayType, self::DISPLAY_TYPES, true)) {
            wp_send_json_error(null, 400);
            return;
        }
        $preferences = ['view' => $view, 'displayType' => $displayType];
        // Do not invoke metadata update hooks for an already confirmed selection.
        if (get_user_option('rrze_statistik_preferences') !== $preferences) {
            update_user_option(get_current_user_id(), 'rrze_statistik_preferences', $preferences, false);
        }
        // A false update result also means unchanged data; compare the stored value.
        if (get_user_option('rrze_statistik_preferences') !== $preferences) {
            wp_send_json_error(null, 500);
            return;
        }
        wp_send_json_success($preferences);
    }
}
