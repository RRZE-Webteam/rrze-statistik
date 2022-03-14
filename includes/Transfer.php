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
        $logs_url = Analytics::retrieveSiteUrl(false, 'logs');
        $script = 'var linechartDataset ='.$json_data.';';
        $script .= 'var abscissaDescriptiontext ='.json_encode($abscissa_desc).';';
        $script .= 'var languagePackage ='.json_encode($languagePackage).';';
        $script .= 'var logsUrl = '.json_encode($logs_url).';';

        wp_add_inline_script('index-js', $script, 'before');
        return $data_body;
    }
}