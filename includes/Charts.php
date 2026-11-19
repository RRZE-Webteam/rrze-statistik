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
            ['wp-a11y'],
            plugin()->getVersion(),
            true
        );
        wp_enqueue_style('rrze-statistik-charts');
        wp_enqueue_script('rrze-statistik-charts');
    }

    /**
     * Renders a progressively enhanced tabbed dashboard with native type selection.
     * Panels remain readable without JavaScript; tabs are revealed by the controller.
     * @return string Escaped controls and server-rendered tab contents.
     */
    public static function dashboard()
    {
        $views = Language::getDashboardViews();
        $preferences = Dashboard::getPreferences();
        $html = '<div class="rrze-statistik-dashboard" data-statistik-dashboard>'
            . '<div class="rrze-statistik-dashboard-controls" hidden>'
            . '<div class="rrze-statistik-tabs" role="tablist" aria-label="'
            . esc_attr(__('Statistics views', 'rrze-statistik')) . '">';
        foreach ($views as $view => $label) {
            $html .= '<button type="button" role="tab" id="rrze-statistik-tab-' . esc_attr($view)
                . '" aria-controls="rrze-statistik-panel-' . esc_attr($view)
                . '" data-statistik-tab="' . esc_attr($view) . '">' . esc_html($label) . '</button>';
        }
        $html .= '</div><div class="rrze-statistik-display-controls">'
            . '<label for="rrze-statistik-display-type">' . esc_html__('Display type', 'rrze-statistik') . '</label>'
            . '<select id="rrze-statistik-display-type" data-statistik-display-type>';
        foreach (Language::getDisplayTypes() as $type => $label) {
            $html .= '<option value="' . esc_attr($type) . '"'
                . ($preferences['displayType'] === $type ? ' selected' : '') . '>' . esc_html($label) . '</option>';
        }
        $html .= '</select></div><p class="rrze-statistik-preferences-progress" role="status"></p>'
            . '<div class="rrze-statistik-preferences-feedback" hidden>'
            . '<p role="status" class="rrze-statistik-preferences-status"></p>'
            . '<button type="button" class="button" data-statistik-retry>'
            . esc_html__('Retry saving', 'rrze-statistik') . '</button></div></div>';
        foreach ($views as $view => $label) {
            $html .= '<section id="rrze-statistik-panel-' . esc_attr($view) . '" data-statistik-panel="'
                . esc_attr($view) . '">';
            if ($view === 'urls') {
                $html .= '<h3>' . esc_html(Language::dashboardWidgetTitle('urls')) . '</h3>'
                    . Analytics::getUrlDatasetTable()
                    . '<p class="rrze-statistik-source"><a href="'
                    . esc_url(preg_replace('/\.tab$/', '.html', Analytics::retrieveSiteUrl('url'))) . '">'
                    . esc_html(Language::getSource()) . '</a></p>';
            } else {
                $html .= self::lineplot($view);
            }
            $html .= '</section>';
        }
        return $html . '</div>';
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
