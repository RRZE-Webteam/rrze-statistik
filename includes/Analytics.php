<?php

namespace RRZE\Statistik;

defined('ABSPATH') || exit;

/**
 * Get processed data & initiate Highchart plot
 */
class Analytics
{
    public function __construct()
    {
        
    }

    public static function getDate()
    {
        $raw_date = date("Ym");
        $new_date = date("Ym", strtotime('-1 month', strtotime($raw_date)));
        return $new_date;
    }

    /**
     * Determine which url should be used and return the string
     *
     * @param string $type
     * @return string
     */
    public static function retrieveSiteUrl($type)
    {
        $debug = Helper::isDebug();
        if ($debug === false) {
            $remove_char = ["https://", "http://", "/"];
            $url = str_replace($remove_char, "", get_site_url());
        } else {
            $url = "www.wordpress.rrze.fau.de";
        }

        if ($type === 'webalizer.hist') {
            $output = 'https://statistiken.rrze.fau.de/webauftritte/logs/' . $url . '/webalizer.hist';
        } else if ($type === 'logs') {
            $output = 'https://statistiken.rrze.fau.de/webauftritte/logs/' . $url;
        } else {
            $output = 'https://statistiken.rrze.fau.de/webauftritte/logs/' . $url . '/url_' . Self::getDate() . '.tab';
        }
        return $output;
    }

    /**
     * Create the Highcharts Linechart using the name of a css-id for the highcharts container.
     *
     * @param string $container
     * @return string
     */
    public function getLinechart($container)
    {
        $remove_char = ["https://", "http://", "/"];
        $site = str_replace($remove_char, "", get_site_url());
        $ready_check = Data::processLinechartDataset(Self::retrieveSiteUrl('webalizer.hist'));
        if ($ready_check === false) {
            return printf(__('It might take a few days until personal statistics for your website ( %1$s ) are displayed within your dashboard.', 'rrze-statistik'), $site) . '</strong><br />';
        } else {
            $this->highcharts = new Highcharts();
            return $this->highcharts->lineplot($container);
        };
    }

    /**
     * Creates a html table for use within the dashboard
     *
     * @param array $array
     * @param string $array_key_1
     * @param string $array_key_2
     * @param string $head_desc_1
     * @param string $head_desc_2
     * @return string
     */
    public static function getTwoDimensionalHtmlTable($array, $array_key_1, $array_key_2, $head_desc_1, $head_desc_2)
    {

        $html = "<div class='rrze-statistik-table'><table><tr><th>" . $head_desc_1 . '</th><th>' . $head_desc_2 . '</th></tr>';

        foreach ($array as $value) {
            $html .= "<tr><td>" . $value[$array_key_1] . '</td><td><a href="' . $value[1] . '">' . htmlspecialchars($value[$array_key_2]) . '</a></td></tr>';
        }
        $html .= "</table></div>";
        return $html;
    }

    /**
     * Gets the html table out of data of the popular sites and images stored inside wp options
     *
     * @return string
     */
    public static function getUrlDatasetTable()
    {
        $data = get_option('rrze_statistik_url_dataset');
        if (!$data) {
            return  __('It might take a few weeks until the summary is displayed on your dashboard.', 'rrze-statistik') . '</strong><br />';
        } else {
            $data_chunks = array_chunk($data, 10);
            
            //if data_chunks is set, create the table

            if(array_key_exists(0, $data_chunks)){
                $top_url = $data_chunks[0];
                $table1 = Self::getTwoDimensionalHtmlTable($top_url, 0, 1, __('Hits', 'rrze-statistik'), __('Sites', 'rrze-statistik'));
                $output = $table1;
            }
            if(array_key_exists(1, $data_chunks)){
                $top_images = $data_chunks[1];
                $table2 = Self::getTwoDimensionalHtmlTable($top_images, 0, 1, __('Hits', 'rrze-statistik'), __('Images', 'rrze-statistik'));
                $output .= $table2;
            }

            return $output;
        }
    }

    /**
     * Checks if the new array's date is newer than the reference date. Checks the last-1 value against each other to ignore incomplete datasets.
     *
     * @param array $array
     * @param array $arrayRef
     * @return boolean
     */
    public static function isDateNewer($array, $arrayRef)
    {
        if (is_array($array) && is_array($arrayRef)){
        $offset = count($array) - 2;
        $date = $array[$offset]['year'] . sprintf("%02d", $array[$offset]['month']);
        $dateRef = $arrayRef[count($arrayRef) - 1]['year'] . sprintf("%02d", $arrayRef[count($arrayRef) - 1]['month']);
        if ((int)$date > (int)$dateRef) {
            return true;
        } else {
            return false;
        }} else {
            return false;
        }
    }
}
