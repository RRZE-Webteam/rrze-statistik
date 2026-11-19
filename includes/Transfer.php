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

        $preferences = Dashboard::getPreferences();
        $displayType = $preferences['displayType'];
        $descriptions = [];
        $descriptionsByType = [];
        foreach (array_keys(Language::getLanguagePackage()) as $metric) {
            $descriptions[$metric] = Language::getLinechartDescription($metric, $displayType);
            foreach (Dashboard::DISPLAY_TYPES as $type) {
                $descriptionsByType[$type][$metric] = Language::getLinechartDescription($metric, $type);
            }
        }

        $payload = [
            'nonce' => wp_create_nonce('rrze_statistik_transfer'),
            'preferencesNonce' => wp_create_nonce('rrze_statistik_preferences'),
            'preferences' => $preferences,
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
            'descriptionsByType' => $descriptionsByType,
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
