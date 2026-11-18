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

require __DIR__ . '/../../includes/Language.php';
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
expect($state['scripts']['rrze-statistik-charts'][1] === ['jquery', 'wp-a11y'], 'WordPress dependencies must be declared.');
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

echo "PHP chart integration checks passed.\n";
