<?php

namespace RRZE\Statistik;

defined('ABSPATH') || exit;

/**
 * Collects Translations in one place.
 */
class Language
{
    public static function getMonthDesc()
    {
        return array(
            __('Jan', 'rrze-statistik'),
            __('Feb', 'rrze-statistik'),
            __('Mar', 'rrze-statistik'),
            __('Apr', 'rrze-statistik'),
            __('May', 'rrze-statistik'),
            __('Jun', 'rrze-statistik'),
            __('Jul', 'rrze-statistik'),
            __('Aug', 'rrze-statistik'),
            __('Sep', 'rrze-statistik'),
            __('Oct', 'rrze-statistik'),
            __('Nov', 'rrze-statistik'),
            __('Dec', 'rrze-statistik'),
        );
    }

    public static function getAbscissa()
    {
        $abscissa_desc = Self::getMonthDesc();
        return $abscissa_desc;
    }

    public static function getSource()
    {
        $source_text = __('Source: Statistiken.rrze.fau.de', 'rrze-statistik');
        return $source_text;
    }

    public static function getLanguagePackage()
    {
        $output = array(
            'visits' => array(
                'ordinate_desc' => __('Visitors', 'rrze-statistik'),
                'headline_chart' => __('Visitors over time', 'rrze-statistik'),
                'tooltip_desc' => __(' Visitors / Month', 'rrze-statistik'),
            ),
            'hits' => array(
                'ordinate_desc' => __('Hits', 'rrze-statistik'),
                'headline_chart' => __('Hits over time', 'rrze-statistik'),
                'tooltip_desc' => __(' Hits / Month', 'rrze-statistik'),
            ),
            'hosts' => array(
                'ordinate_desc' => __('Hosts', 'rrze-statistik'),
                'headline_chart' => __('Hosts over time', 'rrze-statistik'),
                'tooltip_desc' => __(' Hosts / Month', 'rrze-statistik'),
            ),
            'files' => array(
                'ordinate_desc' => __('Files', 'rrze-statistik'),
                'headline_chart' => __('Files over time', 'rrze-statistik'),
                'tooltip_desc' => __(' Files / Month', 'rrze-statistik'),
            ),
            'kbytes' => array(
                'ordinate_desc' => __('Kbytes', 'rrze-statistik'),
                'headline_chart' => __('Kbytes over time', 'rrze-statistik'),
                'tooltip_desc' => __(' Kbytes / Month', 'rrze-statistik'),
            ),
        );
        return $output;
    }

    public static function getLinechartDescription($type)
    {
        switch ($type) {
            case 'visits':
                return __('Line chart displaying the number of site visitors over several months.', 'rrze-statistik');
            case 'hits':
                return __('Line chart displaying the number of hits over several months. A hit is a request for a file such as a web page, image, javascript, or CSS hosted on a web server. A single visitor can generate plenty of hits.', 'rrze-statistik');
            case 'hosts':
                return __('Line chart displaying the number of hosts over several months. A host is a computer or server opening your website. Larger online services are often only displayed as a single host if they share one server.', 'rrze-statistik');
            case 'files':
                return __('Line chart displaying the number of successful loaded files from your domain. This includes all documents, css-files, scripts and media-files.', 'rrze-statistik');
            case 'kbytes':
                return __('Line chart displaying the number of transferred Data in kBytes over several months.', 'rrze-statistik');
        }
    }

    public static function getAccessibilityAbscissa()
    {
        return __('Time over the last 23 months', 'rrze-statistik');
    }

    public static function getCSVButtonText()
    {
        return __('Copy CSV to clipboard', 'rrze_statistik');
    }
}
