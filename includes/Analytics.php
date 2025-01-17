<?php

namespace RRZE\Statistik;

defined('ABSPATH') || exit;

/**
 * Get processed data & initiate Highchart plot
 */
class Analytics
{

    private $highcharts;

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
            $url = "www.wp.rrze.fau.de";
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
        //check if $array is an Array
        if (!is_array($array)) {
            return __('No data available.', 'rrze-statistik');
        } else {
            $html = "<div class='rrze-statistik-table'><table><tr><th>" . $head_desc_1 . '</th><th>' . $head_desc_2 . '</th></tr>';

            foreach ($array as $value) {
                //only execute if $value or $value[array_key_1] or $value[array_key_2] isn't empty
                if (!empty($value) && !empty($value[$array_key_1]) && !empty($value[$array_key_2])) {
                    $html .= '<tr><td>' . $value[$array_key_1] . '</td><td>' . '<a href="' . $value[$array_key_2] . '">' . $value[$array_key_2] . '</a>' . '</td></tr>';
                }
            }
            $html .= "</table></div>";
            return $html;
        }
    }

    /**
     * Gets the html table out of data of the popular sites and images stored inside wp options
     *
     * @return string
     */
    public static function getUrlDatasetTable()
    {
        $output = '';  // Initialize $output
        $data = get_transient('rrze_statistik_data_url');
        if (!$data) {
            return  __('It might take a few weeks until the summary is displayed on your dashboard.', 'rrze-statistik') . '</strong><br />';
        } else {
            if (array_key_exists(0, $data)) {
                $top_url = $data[0];
                if (!empty($top_url)) {
                    $table1 = Self::getTwoDimensionalHtmlTable($top_url, 0, 1, __('Hits', 'rrze-statistik'), __('Sites', 'rrze-statistik'));
                    $output = $table1;
                }
            }
            if (array_key_exists(1, $data)) {
                $top_images = $data[1];
                if (!empty($top_images)) {
                    $table2 = Self::getTwoDimensionalHtmlTable($top_images, 0, 1, __('Hits', 'rrze-statistik'), __('Media', 'rrze-statistik'));
                    $output .= $table2;
                }
            }
            if (array_key_exists(2, $data)) {
                $top_pdf = $data[2];
                if (!empty($top_pdf)) {
                    $table3 = Self::getTwoDimensionalHtmlTable($top_pdf, 0, 1, __('Hits', 'rrze-statistik'), __('Documents', 'rrze-statistik'));
                    $output .= $table3;
                }
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
        if (is_array($array) && is_array($arrayRef)) {
            $offset = count($array) - 2;
            $date = $array[$offset]['year'] . sprintf("%02d", $array[$offset]['month']);
            $dateRef = $arrayRef[count($arrayRef) - 1]['year'] . sprintf("%02d", $arrayRef[count($arrayRef) - 1]['month']);
            if ((int)$date > (int)$dateRef) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
