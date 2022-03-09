<?php

namespace RRZE\Statistik;

defined('ABSPATH') || exit;

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
        $script = 'var linechartDataset ='.$json_data.';';
        $script .= 'var abscissaDescriptiontext ='.json_encode($abscissa_desc).';';
        $script .= 'var languagePackage ='.json_encode($languagePackage).';';

        wp_add_inline_script('index-js', $script, 'before');
        return $data_body;
    }
}