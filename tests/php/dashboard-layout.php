<?php

/** Exercises upgrade layouts and shared multisite user metadata without a database. */
namespace RRZE\Statistik;

define('ABSPATH', __DIR__ . '/');
require __DIR__ . '/../../includes/Dashboard.php';
require __DIR__ . '/../../includes/DashboardLayout.php';

$fixture = [];

/** @return int Current fixture user, independent of the selected site. */
function get_current_user_id() { return $GLOBALS['fixture']['user']; }

/** @param string $key Option name. @return array Site-specific legacy settings. */
function get_option($key) { return $GLOBALS['fixture']['sites'][$GLOBALS['fixture']['site']][$key] ?? []; }

/** @param int $user User ID. @param string $key Global metadata key. @param bool $single Single value. @return mixed Stored value. */
function get_user_meta($user, $key, $single) { return $GLOBALS['fixture']['users'][$user][$key] ?? ''; }

/** @param int $user User ID. @param string $key Global metadata key. @param mixed $value Value. @return bool Write success. */
function update_user_meta($user, $key, $value)
{
    $GLOBALS['fixture']['writes'][] = [$user, $key, $value];
    if ($key === $GLOBALS['fixture']['failKey']) return false;
    $GLOBALS['fixture']['users'][$user][$key] = $value;
    return true;
}

/**
 * Start an isolated upgrade case with unrelated widgets and site preferences.
 * @param string $view Legacy site-wide widget configuration.
 * @param array $layout Core dashboard metadata overrides.
 * @return void
 */
function resetLayout($view = 'display_all', $layout = [])
{
    $GLOBALS['fixture'] = [
        'user' => 12, 'site' => 1, 'writes' => [], 'failKey' => '',
        'sites' => [
            1 => ['rrze_statistik_widget' => ['data_type' => $view]],
            2 => ['rrze_statistik_widget' => ['data_type' => 'urls']],
        ],
        'users' => [
            12 => array_merge([
                'metaboxhidden_dashboard' => ['other-hidden'],
                'closedpostboxes_dashboard' => ['other-closed'],
                'meta-box-order_dashboard' => ['normal' => 'dashboard_activity,rrze_statistik_widget_visits,dashboard_quick_press'],
                'wp_1_rrze_statistik_preferences' => ['view' => 'hits', 'displayType' => 'bar'],
                'wp_2_rrze_statistik_preferences' => ['view' => 'urls', 'displayType' => 'column'],
            ], $layout),
            99 => ['metaboxhidden_dashboard' => ['rrze_statistik_widget_visits']],
        ],
    ];
}

/** @param bool $condition Required outcome. @param string $message Failure context. @return void */
function expectLayout($condition, $message)
{
    if (!$condition) throw new \RuntimeException($message);
}

$id = DashboardLayout::WIDGET_ID;
$prefix = 'rrze_statistik_widget_';

resetLayout('display_all', [
    'metaboxhidden_dashboard' => ['other-hidden', $id],
    'closedpostboxes_dashboard' => ['other-closed', $prefix . 'hits'],
    'meta-box-order_dashboard' => [
        'normal' => 'dashboard_activity,' . $id . ',dashboard_quick_press',
        'side' => $prefix . 'hits,dashboard_primary,' . $prefix . 'urls,' . $prefix . 'hits',
    ],
]);
$before = $fixture['users'];
DashboardLayout::migrate();
$after = $fixture['users'][12];
expectLayout($after['metaboxhidden_dashboard'] === ['other-hidden'], 'Visible hits must keep the unified widget visible despite hidden visits.');
expectLayout($after['closedpostboxes_dashboard'] === ['other-closed', $id], 'The visible anchor must supply the collapsed state.');
expectLayout($after['meta-box-order_dashboard'] === [
    'normal' => 'dashboard_activity,dashboard_quick_press', 'side' => $id . ',dashboard_primary',
], 'The first visible widget must supply its position; obsolete and duplicate IDs must disappear.');
expectLayout($fixture['users'][99] === $before[99], 'Another user must not be changed.');
foreach (['wp_1_rrze_statistik_preferences', 'wp_2_rrze_statistik_preferences'] as $key) {
    expectLayout($after[$key] === $before[12][$key], 'Per-site chart preferences must remain separate from user-wide layout.');
}
$fixture['users'][12]['metaboxhidden_dashboard'][] = $id;
$fixture['site'] = 2;
$writes = count($fixture['writes']);
DashboardLayout::migrate();
expectLayout(count($fixture['writes']) === $writes, 'A second site or page load must not rerun the global layout migration.');
expectLayout(in_array($id, $fixture['users'][12]['metaboxhidden_dashboard'], true), 'A later manual visibility change must survive.');

resetLayout('visits_and_sites', [
    'metaboxhidden_dashboard' => ['other-hidden', $id, $prefix . 'urls'],
    'closedpostboxes_dashboard' => ['other-closed', $prefix . 'urls'],
    'meta-box-order_dashboard' => ['side' => 'dashboard_primary,' . $prefix . 'urls,' . $id],
]);
DashboardLayout::migrate();
expectLayout($fixture['users'][12]['metaboxhidden_dashboard'] === ['other-hidden', $id], 'All offered widgets hidden must remain hidden, regardless of other unused IDs.');
expectLayout($fixture['users'][12]['closedpostboxes_dashboard'] === ['other-closed', $id], 'The first ordered hidden widget supplies the collapsed state when all are hidden.');
expectLayout($fixture['users'][12]['meta-box-order_dashboard']['side'] === 'dashboard_primary,' . $id, 'The first old position must be retained when all widgets were hidden.');

resetLayout('hits', [
    'metaboxhidden_dashboard' => ['other-hidden', $id, $prefix . 'hits'],
    'meta-box-order_dashboard' => ['normal' => $id . ',dashboard_activity,' . $prefix . 'hits'],
]);
DashboardLayout::migrate();
expectLayout($fixture['users'][12]['metaboxhidden_dashboard'] === ['other-hidden', $id], 'Single-metric installations must use only the previously offered widget for visibility.');
expectLayout($fixture['users'][12]['meta-box-order_dashboard']['normal'] === 'dashboard_activity,' . $id, 'A stale visits position must not replace the single-metric position.');

resetLayout('display_all', [
    'closedpostboxes_dashboard' => ['other-closed', $id],
    'meta-box-order_dashboard' => ['normal' => $prefix . 'files,dashboard_activity,' . $id],
]);
DashboardLayout::migrate();
expectLayout($fixture['users'][12]['closedpostboxes_dashboard'] === ['other-closed'], 'An expanded first visible widget must override the stale collapsed visits state.');

resetLayout('visits', [
    'metaboxhidden_dashboard' => '', 'closedpostboxes_dashboard' => false, 'meta-box-order_dashboard' => 'invalid',
]);
DashboardLayout::migrate();
expectLayout(count($fixture['writes']) === 1 && $fixture['writes'][0][1] === DashboardLayout::MIGRATION_KEY, 'Fresh or malformed layouts must use core defaults without manufacturing layout data.');

foreach (['closedpostboxes_dashboard', DashboardLayout::MIGRATION_KEY] as $failure) {
    resetLayout('display_all', [
        'metaboxhidden_dashboard' => [$id],
        'closedpostboxes_dashboard' => [$prefix . 'hits'],
        'meta-box-order_dashboard' => ['side' => $prefix . 'hits,' . $id],
    ]);
    $original = $fixture['users'][12];
    $fixture['failKey'] = $failure;
    DashboardLayout::migrate();
    expectLayout($fixture['users'][12] === $original, 'Failed writes must restore the original layout and permit a retry.');
    $fixture['failKey'] = '';
    DashboardLayout::migrate();
    expectLayout(get_user_meta(12, DashboardLayout::MIGRATION_KEY, true) === DashboardLayout::VERSION, 'A recovered write must finish the migration.');
}

resetLayout();
$fixture['user'] = 0;
DashboardLayout::migrate();
expectLayout(!$fixture['writes'], 'Anonymous requests must never migrate user metadata.');

echo "Dashboard layout migration checks passed.\n";
