<?php

namespace RRZE\Statistik;

defined('ABSPATH') || exit;

/**
 * Sends PHP values as JSON to highchartsIndex.js
 */
class Transfer
{
    /**
     * Sends all parameters to JS
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
        $display_type = $option['display_type'];

        $rrze_statistik_transfer_nonce = wp_create_nonce( "rrze_statistik_transfer");
        $ajax_url = admin_url('admin-ajax.php');
        

        $script = 'var linechartDataset =' . $json_data . ';';
        $script .= 'var abscissaDescriptiontext =' . json_encode($abscissa_desc) . ';';
        $script .= 'var languagePackage =' . json_encode($languagePackage) . ';';
        $script .= 'var abscissaTitle =' .json_encode($abscissa_title) . ';';
        $script .= 'var sourceText = ' . json_encode($source_Text) . ';';
        $script .= 'var a11yAbscissa = ' . json_encode($a11y_abscissa) . ';';
        $script .= 'var displayType = ' . json_encode($display_type) . ';';
        //wp_add_inline_script('index-js', $script, 'before');
        wp_add_inline_script('index-js', 'const RRZESTATISTIKTRANSFER =' .json_encode([
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

    public static function refreshVariables(){
        $option = get_option('rrze_statistik_widget');
        $display_type = $option['display_type'];

        $script = 'var displayType = ' . json_encode($display_type) . ';';
        wp_add_inline_script('index-js', $script, 'before');
    }
}
