<?php

/**
 * Exercises the plugin in a disposable WordPress installation, using real core APIs.
 * Run through wp eval-file after activation. Never run against a user's live site.
 */
if (!defined('RRZE_STATISTIK_TEST_SITE') || RRZE_STATISTIK_TEST_SITE !== true) {
    throw new RuntimeException('This check requires an explicitly disposable WordPress test site.');
}
require_once ABSPATH . 'wp-admin/includes/admin.php';

/** @param bool $condition Required outcome. @param string $message Failure context. @return void */
function rrze_statistik_smoke_expect($condition, $message)
{
    if (!$condition) throw new RuntimeException($message);
}

$admin = get_user_by('login', 'test-admin');
rrze_statistik_smoke_expect((bool) $admin, 'The disposable test administrator must exist.');
wp_set_current_user($admin->ID);
update_option('rrze_statistik_widget', ['data_type' => 'display_all', 'display_type' => 'bar']);
delete_user_meta($admin->ID, \RRZE\Statistik\DashboardLayout::MIGRATION_KEY);
update_user_meta($admin->ID, 'metaboxhidden_dashboard', ['rrze_statistik_widget_visits', 'other-widget']);
update_user_meta($admin->ID, 'meta-box-order_dashboard', [
    'normal' => 'dashboard_activity,rrze_statistik_widget_hits,rrze_statistik_widget_visits',
]);
set_transient('rrze_statistik_data_webalizer_hist', [
    ['year' => wp_date('Y'), 'month' => '1', 'visits' => '100', 'hits' => '1000', 'hosts' => '50', 'files' => '700', 'kbytes' => '8000'],
], HOUR_IN_SECONDS);
set_transient('rrze_statistik_data_url', [[[100, '/']], [[25, '/image.png']], [[10, '/report.pdf']]], HOUR_IN_SECONDS);

require_once ABSPATH . 'wp-admin/includes/dashboard.php';
set_current_screen('dashboard');
do_action('admin_enqueue_scripts', 'index.php');
wp_dashboard_setup();
rrze_statistik_smoke_expect(get_user_meta($admin->ID, 'metaboxhidden_dashboard', true) === ['other-widget'], 'A visible legacy metric must keep the consolidated widget visible.');
rrze_statistik_smoke_expect(get_user_meta($admin->ID, 'meta-box-order_dashboard', true)['normal'] === 'dashboard_activity,rrze_statistik_widget_visits', 'Core must receive the migrated widget order.');
rrze_statistik_smoke_expect(wp_script_is('rrze-statistik-charts', 'enqueued'), 'The chart bundle must be enqueued.');
rrze_statistik_smoke_expect(wp_style_is('rrze-statistik-charts', 'enqueued'), 'The stylesheet must be enqueued.');

ob_start();
(new \RRZE\Statistik\Dashboard())->render();
$html = ob_get_clean();
rrze_statistik_smoke_expect(substr_count($html, 'data-statistik-panel=') === 6, 'All statistics panels must render.');
rrze_statistik_smoke_expect(strpos($html, 'rrze-statistik-ranking') !== false, 'Real cached popular pages must render.');
$before = wp_scripts()->get_data('rrze-statistik-charts', 'before');
rrze_statistik_smoke_expect(is_array($before) && strpos(implode('', $before), 'RRZESTATISTIKTRANSFER') !== false, 'The chart data must be attached to the real registered bundle.');

update_user_option($admin->ID, 'rrze_statistik_preferences', ['view' => 'hosts', 'displayType' => 'column'], false);
rrze_statistik_smoke_expect(\RRZE\Statistik\Dashboard::getPreferences()['view'] === 'hosts', 'Real per-site preference storage must override legacy defaults.');
update_user_meta($admin->ID, 'metaboxhidden_dashboard', ['other-widget', 'rrze_statistik_widget_visits']);
\RRZE\Statistik\DashboardLayout::migrate();
rrze_statistik_smoke_expect(in_array('rrze_statistik_widget_visits', get_user_meta($admin->ID, 'metaboxhidden_dashboard', true), true), 'A completed migration must respect later user choices.');

echo 'WordPress ' . $GLOBALS['wp_version'] . ' / PHP ' . PHP_VERSION . " smoke checks passed.\n";
