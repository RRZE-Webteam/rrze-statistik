<?php

namespace RRZE\Statistik;

defined('ABSPATH') || exit;

/**
 * Registers the chart assets and renders their progressively enhanced containers.
 */
class Charts
{
    /**
     * Whether the asset hook has already been registered during this request.
     *
     * @var bool
     */
    private static $hooksRegistered = false;

    /**
     * Registers the dashboard asset hook once, even if instantiated repeatedly.
     */
    public function __construct()
    {
        if (!self::$hooksRegistered) {
            add_action('admin_enqueue_scripts', [$this, 'registerAssets']);
            self::$hooksRegistered = true;
        }
    }

    /**
     * Loads the locally bundled charts on the site dashboard only.
     *
     * @param string $hookSuffix Current administration page hook suffix.
     * @return void
     */
    public function registerAssets($hookSuffix)
    {
        $screen = get_current_screen();
        if (!$screen || $screen->id !== 'dashboard') {
            return;
        }

        wp_register_style(
            'rrze-statistik-charts',
            plugins_url('dist/charts.css', plugin()->getBasename()),
            [],
            plugin()->getVersion()
        );
        wp_register_script(
            'rrze-statistik-charts',
            plugins_url('dist/chartsIndex.js', plugin()->getBasename()),
            ['jquery', 'wp-a11y'],
            plugin()->getVersion(),
            true
        );
        wp_enqueue_style('rrze-statistik-charts');
        wp_enqueue_script('rrze-statistik-charts');
    }

    /**
     * Renders a chart mount point and a source fallback for one supported metric.
     *
     * The source link is outside the mount point so it survives a script failure.
     *
     * @param string $container Metric identifier and the original chart element ID.
     * @return string Escaped dashboard HTML, or an empty string for an unknown metric.
     */
    public static function lineplot($container)
    {
        $metrics = Language::getLanguagePackage();
        if (!is_string($container) || !isset($metrics[$container])) {
            return '';
        }

        return '<div class="rrze-statistik">'
            . '<div class="rrze-statistik-chart" id="' . esc_attr($container)
            . '" data-statistik-chart data-metric="' . esc_attr($container) . '">'
            . '<p>' . esc_html__('Loading statistics…', 'rrze-statistik') . '</p></div>'
            . '<noscript><p>'
            . esc_html__('JavaScript is required for the charts. The source statistics are available using the following link.', 'rrze-statistik')
            . '</p></noscript>'
            . '<p class="rrze-statistik-source"><a href="'
            . esc_url(Analytics::retrieveSiteUrl('logs')) . '">'
            . esc_html(Language::getSource()) . '</a></p></div>';
    }
}
