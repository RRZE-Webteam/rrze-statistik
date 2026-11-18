<?php

namespace RRZE\Statistik;

defined('ABSPATH') || exit;

/**
 * Publishes cached chart data and WordPress-localized metadata to the browser.
 */
class Transfer
{
    /**
     * Whether the shared data object has been attached to the script this request.
     *
     * @var bool
     */
    private static $sent = false;

    /**
     * Adds one shared data object before the chart bundle, preserving legacy fields.
     *
     * Each widget calls this method, but all widgets consume the same dataset.
     *
     * @param array $dataBody Cached monthly records; non-arrays are treated as empty.
     * @param array $abscissaDesc Translated month abbreviations.
     * @param array $languagePackage Translated metric titles and units.
     * @return array Normalized records, retaining the previous return contract.
     */
    public static function sendToJs($dataBody, $abscissaDesc, $languagePackage)
    {
        $dataBody = is_array($dataBody) ? array_values($dataBody) : [];
        if (self::$sent) {
            return $dataBody;
        }

        $options = get_option('rrze_statistik_widget');
        $displayType = is_array($options) ? ($options['display_type'] ?? 'spline') : 'spline';
        if (!in_array($displayType, ['spline', 'areaspline', 'column', 'bar'], true)) {
            $displayType = 'spline';
        }
        $descriptions = [];
        foreach (array_keys(Language::getLanguagePackage()) as $metric) {
            $descriptions[$metric] = Language::getLinechartDescription($metric);
        }

        $payload = [
            'nonce' => wp_create_nonce('rrze_statistik_transfer'),
            'ajaxurl' => admin_url('admin-ajax.php'),
            'linechartDataset' => $dataBody,
            'abscissaDescriptiontext' => is_array($abscissaDesc) ? $abscissaDesc : Language::getAbscissa(),
            'languagePackage' => is_array($languagePackage) ? $languagePackage : Language::getLanguagePackage(),
            'abscissaTitle' => Language::getAbscissaTitle(),
            'sourceText' => Language::getSource(),
            'a11yAbscissa' => Language::getAccessibilityAbscissa(),
            'displayType' => $displayType,
            'currentYear' => (int) wp_date('Y'),
            'locale' => str_replace('_', '-', get_user_locale()),
            'sourceUrl' => Analytics::retrieveSiteUrl('logs'),
            'descriptions' => $descriptions,
            'labels' => Language::getChartLabels(),
        ];
        $json = wp_json_encode($payload, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        if ($json !== false) {
            self::$sent = wp_add_inline_script(
                'rrze-statistik-charts',
                'var RRZESTATISTIKTRANSFER = ' . $json . ';',
                'before'
            );
        }

        return $dataBody;
    }
}
