<?php

namespace RRZE\Statistik;

defined('ABSPATH') || exit;

/**
 * Sends PHP values as JSON to highchartsIndex.js
 */
class Transfer
{
    /**
     * Sends all parameters to JS via Ajax-Request
     *
     * @param array $data_body
     * @param array $abscissa_desc
     * @param string $ordinate_desc
     * @param string $headline_chart
     * @param string $tooltip_desc
     * @return void
     */
    public static function sendToJs($data_body, $abscissa_desc, $languagePackage)
    {
        $json_data = json_encode($data_body);
        $source_Text = Language::getSource();
        $a11y_abscissa = Language::getAccessibilityAbscissa();
        $abscissa_title = Language::getAbscissaTitle();
        $option = get_option('rrze_statistik_widget');
        $display_type = $option['display_type'] ?? 'Spline';

        $rrze_statistik_transfer_nonce = wp_create_nonce("rrze_statistik_transfer");
        $ajax_url = admin_url('admin-ajax.php');

        wp_add_inline_script('index-js', 'var RRZESTATISTIKTRANSFER =' . json_encode([
            'nonce' => $rrze_statistik_transfer_nonce,
            'ajaxurl' => $ajax_url,
            'linechartDataset' => json_decode($json_data),
            'abscissaDescriptiontext' => $abscissa_desc,
            'languagePackage' => $languagePackage,
            'abscissaTitle' => $abscissa_title,
            'sourceText' => $source_Text,
            'a11yAbscissa' => $a11y_abscissa,
            'displayType' => $display_type
        ]) . ';', 'before');

        return $data_body;
    }
}
