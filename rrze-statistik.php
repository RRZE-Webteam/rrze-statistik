<?php

/*
Plugin Name:     RRZE Statistik
Plugin URI:      https://github.com/rrze-webteam/rrze-statistik
Description:     Anzeige der Statistiken im Dashboard
Version:         0.0.1
Author:          RRZE Webteam
Author URI:      https://blogs.fau.de/webworking/
License:         GNU General Public License v2
License URI:     http://www.gnu.org/licenses/gpl-2.0.html
Text Domain:     rrze-statistik
*/

namespace RRZE\statistik;

defined('ABSPATH') || exit;

use RRZE\statistik\Main;

const RRZE_HIGHCHARTS_VERSION = '9.3.3';

const RRZE_PHP_VERSION = '7.4';
const RRZE_WP_VERSION = '5.8';
const RRZE_PLUGIN_FILE = __FILE__;

spl_autoload_register(function ($class) {
    $prefix = __NAMESPACE__;
    $base_dir = __DIR__ . '/includes/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

register_activation_hook(__FILE__, __NAMESPACE__ . '\activation');
register_deactivation_hook(__FILE__, __NAMESPACE__ . '\deactivation');

add_action('plugins_loaded', __NAMESPACE__ . '\loaded');

/**
 * Load Text Domain for Translations
 * @return void
 */
function loadTextdomain()
{
    load_plugin_textdomain('rrze-statistik', false, sprintf('%s/languages/', dirname(plugin_basename(__FILE__))));
}

/**
 * Check for System requirements
 * @return string error message
 */
function systemRequirements()
{
    $error = '';
    if (version_compare(PHP_VERSION, RRZE_PHP_VERSION, '<')) {
        $error = sprintf(__('The server is running PHP version %1$s. The Plugin requires at least PHP version %2$s.', 'rrze-statistik'), PHP_VERSION, RRZE_PHP_VERSION);
    } elseif (version_compare($GLOBALS['wp_version'], RRZE_WP_VERSION, '<')) {
        $error = sprintf(__('The server is running WordPress version %1$s. The Plugin requires at least WordPress version %2$s.', 'rrze-statistik'), $GLOBALS['wp_version'], RRZE_WP_VERSION);
    }
    return $error;
}

function activation()
{
    loadTextdomain();

    if ($error = systemRequirements()) {
        deactivate_plugins(plugin_basename(__FILE__), false, true);
        wp_die($error);
    }
}

/**
 * Handler for Deactivation
 * @return void
 */
function deactivation()
{
    flush_rewrite_rules();
}

/**
 * Once loaded, run
 */
function loaded()
{
    loadTextdomain();

    if ($error = systemRequirements()) {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        $plugin_data = get_plugin_data(__FILE__);
        $plugin_name = $plugin_data['Name'];
        $tag = is_network_admin() ? 'network_admin_notices' : 'admin_notices';
        add_action($tag, function () use ($plugin_name, $error) {
            printf('<div class="notice notice-error"><p>%1$s: %2$s</p></div>', esc_html($plugin_name), esc_html($error));
        });
    } else {
        // Hauptklasse (Main) wird instanziiert.
        $main = new Main(plugin_basename(__FILE__));
        $main->onLoaded();
    }
}
