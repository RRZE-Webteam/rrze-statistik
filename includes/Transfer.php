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
        $display_type = get_option('rrze_statistik_widget')['display_type'];

        $script = 'var linechartDataset =' . $json_data . ';';
        $script .= 'var abscissaDescriptiontext =' . json_encode($abscissa_desc) . ';';
        $script .= 'var languagePackage =' . json_encode($languagePackage) . ';';
        $script .= 'var abscissaTitle =' .json_encode($abscissa_title) . ';';
        $script .= 'var sourceText = ' . json_encode($source_Text) . ';';
        $script .= 'var a11yAbscissa = ' . json_encode($a11y_abscissa) . ';';
        $script .= 'var displayType = ' . json_encode($display_type) . ';';

        wp_add_inline_script('index-js', $script, 'before');
        return $data_body;
    }
}
