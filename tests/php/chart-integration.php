<?php

/**
 * Exercises chart integration without booting WordPress or touching its database.
 *
 * Run with: php tests/php/chart-integration.php
 */

namespace RRZE\Statistik;

define('ABSPATH', __DIR__ . '/');

$state = [
    'screen' => (object) ['id' => 'dashboard'],
    'actions' => [],
    'scripts' => [],
    'styles' => [],
    'enqueued' => [],
    'inline' => [],
    'options' => ['display_type' => 'column'],
    'transient' => [['year' => '2026', 'month' => '3', 'visits' => '0']],
    'acceptInline' => false,
    'personal' => false,
    'canRead' => true,
    'widgets' => [],
    'userUpdates' => [],
    'storageFailure' => false,
];

/**
 * Provides the plugin metadata needed by the asset registration code.
 */
class TestPlugin
{
    /**
     * @return string Plugin basename.
     */
    public function getBasename() { return 'rrze-statistik/rrze-statistik.php'; }

    /**

     * @return string Plugin version.

     */
    public function getVersion() { return '1.1.16'; }
}

/**
 * Keeps fixture URL selection deterministic without loading the real helper.
 */
class Helper
{
    /**
     * @return bool Whether fixture URLs are selected.
     */
    public static function isDebug() { return true; }
}

/**

 * @return TestPlugin Plugin metadata fixture.

 */
function plugin() { return new TestPlugin(); }

/**

 * @param string $text Message.

 * @param string $domain Text domain.

 * @return string Message.

 */
function __($text, $domain) { return $text; }

/**

 * @param string $text Text to escape.

 * @return string Escaped HTML.

 */
function esc_html($text) { return htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); }

/**

 * @param string $text Text to escape.

 * @return string Escaped attribute.

 */
function esc_attr($text) { return esc_html($text); }

/**

 * @param string $url URL to escape.

 * @return string Escaped URL.

 */
function esc_url($url) { return esc_html($url); }

/**

 * @param string $text Message.

 * @param string $domain Text domain.

 * @return string Escaped message.

 */
function esc_html__($text, $domain) { return esc_html(__($text, $domain)); }

/**

 * @param string $name Hook name.

 * @param callable $callback Callback.

 * @return void

 */
function add_action($name, $callback) { $GLOBALS['state']['actions'][] = [$name, $callback]; }

/**

 * @return object|null Current screen fixture.

 */
function get_current_screen() { return $GLOBALS['state']['screen']; }

/**

 * @param string $path Asset path.

 * @param string $plugin Plugin basename.

 * @return string URL.

 */
function plugins_url($path, $plugin) { return 'https://example.test/plugins/rrze-statistik/' . $path; }

/**

 * @param string $handle Handle.

 * @param string $url URL.

 * @param array $deps Dependencies.

 * @param string $version Version.

 * @return void

 */
function wp_register_style($handle, $url, $deps, $version) { $GLOBALS['state']['styles'][$handle] = [$url, $deps, $version]; }

/**

 * @param string $handle Handle.

 * @param string $url URL.

 * @param array $deps Dependencies.

 * @param string $version Version.

 * @param bool $footer Footer flag.

 * @return void

 */
function wp_register_script($handle, $url, $deps, $version, $footer) { $GLOBALS['state']['scripts'][$handle] = [$url, $deps, $version, $footer]; }

/**

 * @param string $handle Style handle.

 * @return void

 */
function wp_enqueue_style($handle) { $GLOBALS['state']['enqueued'][] = 'style:' . $handle; }

/**

 * @param string $handle Script handle.

 * @return void

 */
function wp_enqueue_script($handle) { $GLOBALS['state']['enqueued'][] = 'script:' . $handle; }

/**

 * @param string $name Option name.

 * @return array Stored settings fixture.

 */
function get_option($name) { return $GLOBALS['state']['options']; }

/**

 * @param string $format Date format.

 * @return string Calendar year fixture.

 */
function wp_date($format) { return '2026'; }

/**

 * @return string User locale fixture.

 */
function get_user_locale() { return 'de_DE'; }

/**

 * @param string $action Nonce action.

 * @return string Nonce fixture.

 */
function wp_create_nonce($action) { return 'fixture-nonce'; }

/**

 * @param string $path Admin path.

 * @return string Admin URL.

 */
function admin_url($path) { return 'https://example.test/wp-admin/' . $path; }

/**

 * @param mixed $data JSON value.

 * @param int $flags JSON flags.

 * @return string|false Encoded JSON.

 */
function wp_json_encode($data, $flags) { return json_encode($data, $flags); }

/**

 * @param string $handle Handle.

 * @param string $data Inline JavaScript.

 * @param string $position Placement.

 * @return bool Whether attachment succeeded.

 */
function wp_add_inline_script($handle, $data, $position)
{
    if (!$GLOBALS['state']['acceptInline']) {
        return false;
    }
    $GLOBALS['state']['inline'][] = [$handle, $data, $position];
    return true;
}

/**

 * @return string Site URL containing characters that must be escaped.

 */
function get_site_url() { return 'https://example.test/<script>alert(1)</script>'; }

/**

 * @param string $name Transient name.

 * @return array|false Cached dataset fixture.

 */
function get_transient($name) { return $GLOBALS['state']['transient']; }

/**

 * @param bool $condition Expected condition.

 * @param string $message Failure description.

 * @return void

 */
function expect($condition, $message)
{
    if (!$condition) {
        throw new \RuntimeException($message);
    }
}

/** @return array|false Current user's site-scoped preferences. */
function get_user_option($name) { return $GLOBALS['state']['personal']; }

/** @return int Authenticated user fixture. */
function get_current_user_id() { return 12; }

/** @param string $capability Required capability. @return bool Access fixture. */
function current_user_can($capability) { return $capability === 'read' && $GLOBALS['state']['canRead']; }

/**
 * Record only site-scoped writes for the authenticated user.
 * @param int $user User ID.
 * @param string $key Preference name.
 * @param array $value Preferences.
 * @param bool $global Network-wide flag.
 * @return bool Whether the fixture accepted the update.
 */
function update_user_option($user, $key, $value, $global)
{
    $GLOBALS['state']['userUpdates'][] = [$user, $key, $value, $global];
    if ($GLOBALS['state']['storageFailure']) return false;
    $GLOBALS['state']['personal'] = $value;
    return true;
}

/** @param string $name Option key. @param array $value Options. @return void */
function update_option($name, $value) { $GLOBALS['state']['options'] = $value; }

/**
 * Capture dashboard registration without a running WordPress instance.
 * @param string $id Widget identifier.
 * @param string $title Widget heading.
 * @param callable $render Render callback.
 * @param callable|null $configure Optional legacy configuration callback.
 * @return void
 */
function wp_add_dashboard_widget($id, $title, $render, $configure = null)
{
    $GLOBALS['state']['widgets'][] = [$id, $title, $render, $configure];
}

/** @param string $action Expected nonce action. @param string $key POST key. @return void */
function check_ajax_referer($action, $key)
{
    if ($action !== 'rrze_statistik_preferences' || ($_POST[$key] ?? '') !== 'valid') {
        wp_send_json_error(null, 403);
    }
}

/** @param mixed $data Response data. @param int $status HTTP status. @return void */
function wp_send_json_error($data, $status) { throw new \RuntimeException('response', $status); }

/** @param mixed $data Response data. @return void */
function wp_send_json_success($data) { throw new \RuntimeException('response', 200); }

/** @param Dashboard $dashboard Endpoint owner. @return int Captured HTTP response status. */
function saveResponse($dashboard)
{
    try { $dashboard->savePreferences(); } catch (\RuntimeException $response) { return $response->getCode(); }
    return 0;
}

require __DIR__ . '/../../includes/Language.php';
require __DIR__ . '/../../includes/Dashboard.php';
require __DIR__ . '/../../includes/Charts.php';
require __DIR__ . '/../../includes/Transfer.php';
require __DIR__ . '/../../includes/Analytics.php';
require __DIR__ . '/../../includes/Data.php';

$charts = new Charts();
new Charts();
expect(count($state['actions']) === 1, 'The asset hook must be registered once.');
$state['screen'] = (object) ['id' => 'plugins'];
$charts->registerAssets('plugins.php');
expect($state['scripts'] === [], 'Other administration pages must not receive chart assets.');
$state['screen'] = (object) ['id' => 'dashboard'];
$charts->registerAssets('index.php');
expect($state['scripts']['rrze-statistik-charts'][1] === ['wp-a11y'], 'WordPress dependencies must be declared.');
expect(substr($state['scripts']['rrze-statistik-charts'][0], -19) === 'dist/chartsIndex.js', 'The neutral chart bundle must be registered.');

$markup = Charts::lineplot('visits');
expect(strpos($markup, 'id="visits"') !== false, 'Existing metric IDs must remain stable.');
expect(strpos($markup, 'data-statistik-chart') !== false, 'Chart mount points must be discoverable.');
expect(strpos($markup, '<noscript>') !== false, 'A no-JavaScript fallback must exist.');
expect(Charts::lineplot('"><script>') === '', 'Unknown metrics must not generate markup.');

$rows = [['year' => '2026', 'month' => '3', 'visits' => '0', 'extra' => '</script><script>bad()</script>']];
Transfer::sendToJs($rows, Language::getAbscissa(), Language::getLanguagePackage());
expect($state['inline'] === [], 'A failed script attachment must not be considered sent.');
$state['acceptInline'] = true;
Transfer::sendToJs($rows, Language::getAbscissa(), Language::getLanguagePackage());
Transfer::sendToJs($rows, Language::getAbscissa(), Language::getLanguagePackage());
expect(count($state['inline']) === 1, 'Multiple widgets must receive one shared payload.');
$script = $state['inline'][0][1];
expect(strpos($script, '</script>') === false, 'Payload content must not terminate its script element.');
$payload = json_decode(substr($script, strlen('var RRZESTATISTIKTRANSFER = '), -1), true);
expect($payload['currentYear'] === 2026 && $payload['locale'] === 'de-DE', 'Calendar year and user locale must be supplied.');
expect($payload['displayType'] === 'column', 'Existing display options must be retained.');
expect($payload['linechartDataset'][0]['visits'] === '0', 'Zero-valued source records must be retained.');
expect(isset($payload['labels']['navigationHelp'], $payload['descriptions']['visits']), 'Translated accessibility metadata must be supplied.');
expect(strpos($payload['a11yAbscissa'], '2024') !== false, 'The accessible range must describe three calendar years.');

$state['transient'] = false;
$analytics = new Analytics();
ob_start();
$notice = $analytics->getLinechart('visits');
$leakedOutput = ob_get_clean();
expect($leakedOutput === '', 'The rendering method must return rather than echo its missing-data notice.');
expect(strpos($notice, '<script>') === false, 'Website names must be escaped in missing-data notices.');

$dashboard = new Dashboard();
foreach (['display_all', 'visits_and_sites', 'visits', 'hits', 'hosts', 'files', 'kbytes', 'urls'] as $legacyView) {
    $state['options'] = ['data_type' => $legacyView, 'display_type' => 'bar'];
    $state['widgets'] = [];
    $dashboard->add_rrze_statistik_dashboard_widget();
    expect(count($state['widgets']) === 1, 'Every legacy mode must register exactly one dashboard widget.');
    expect($state['widgets'][0][3] === null, 'The separate Configure screen must be removed.');
    $expectedView = in_array($legacyView, Dashboard::VIEWS, true) ? $legacyView : 'visits';
    expect(Dashboard::getPreferences() === ['view' => $expectedView, 'displayType' => 'bar'], 'Legacy defaults must migrate predictably.');
}
$state['personal'] = ['view' => 'hosts', 'displayType' => 'column'];
expect(Dashboard::getPreferences()['view'] === 'hosts', 'Personal views must override the legacy default.');
$state['personal'] = ['view' => ['invalid'], 'displayType' => 'invalid'];
$state['options'] = false;
expect(Dashboard::getPreferences() === ['view' => 'visits', 'displayType' => 'spline'], 'Malformed options must fall back safely.');
$markup = Charts::dashboard();
expect(substr_count($markup, 'role="tab"') === 6, 'All five metrics and popular pages must have tabs.');
expect(substr_count($markup, 'data-statistik-panel=') === 6, 'Each tab must have one panel.');
expect(strpos($markup, 'rrze-statistik-dashboard-controls" hidden') !== false, 'Inactive JavaScript controls must be hidden in the fallback.');
expect(isset($payload['descriptionsByType']['bar']['visits']), 'Changing chart type must have localized descriptions.');

$_POST = ['nonce' => 'valid', 'view' => 'hits', 'displayType' => 'bar'];
expect(saveResponse($dashboard) === 200, 'Valid selections must be saved.');
expect($state['userUpdates'][0] === [12, 'rrze_statistik_preferences', ['view' => 'hits', 'displayType' => 'bar'], false], 'Writes must be restricted to the current user and site.');
expect($state['options'] === false, 'Personal selections must not change the site-wide legacy options.');
expect(saveResponse($dashboard) === 200, 'Saving an unchanged selection must succeed.');
expect(count($state['userUpdates']) === 1, 'Unchanged selections must not trigger metadata writes again.');
$before = count($state['userUpdates']);
$_POST['view'] = ['visits'];
expect(saveResponse($dashboard) === 400, 'Array inputs must be rejected.');
$_POST['view'] = 'unknown';
expect(saveResponse($dashboard) === 400, 'Unsupported views must be rejected.');
$_POST['view'] = 'visits';
$_POST['displayType'] = 'unknown';
expect(saveResponse($dashboard) === 400, 'Unsupported chart types must be rejected.');
$_POST['displayType'] = 'spline';
$_POST['nonce'] = 'invalid';
expect(saveResponse($dashboard) === 403, 'An invalid nonce must not write preferences.');
$_POST['nonce'] = 'valid';
$state['canRead'] = false;
expect(saveResponse($dashboard) === 403, 'Users without dashboard access must not write preferences.');
expect(count($state['userUpdates']) === $before, 'Rejected requests must not write any data.');
$state['canRead'] = true;
$state['storageFailure'] = true;
expect(saveResponse($dashboard) === 500, 'A failed write must be reported rather than silently accepted.');

echo "PHP chart and dashboard integration checks passed.\n";
