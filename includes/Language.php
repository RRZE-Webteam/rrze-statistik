<?php

namespace RRZE\Statistik;

defined('ABSPATH') || exit;

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

    public static function getLanguagePackage()
    {
        $output = array(
            'visits' => array(
                'ordinate_desc' => __('Visitors', 'rrze-statistik'),
                'headline_chart' => __('Visitors (last 24 months)', 'rrze-statistik'),
                'tooltip_desc' => __(' Visitors / Month', 'rrze-statistik'),
            ),
            'hits' => array(
                'ordinate_desc' => __('Hits', 'rrze-statistik'),
                'headline_chart' => __('Hits (last 24 months)', 'rrze-statistik'),
                'tooltip_desc' => __(' Hits / Month', 'rrze-statistik'),
            ),
            'hosts' => array(
                'ordinate_desc' => __('Hosts', 'rrze-statistik'),
                'headline_chart' => __('Hosts (last 24 months)', 'rrze-statistik'),
                'tooltip_desc' => __(' Hosts / Month', 'rrze-statistik'),
            ),
            'files' => array(
                'ordinate_desc' => __('Files', 'rrze-statistik'),
                'headline_chart' => __('Files (last 24 months)', 'rrze-statistik'),
                'tooltip_desc' => __(' Files / Month', 'rrze-statistik'),
            ),
            'kbytes' => array(
                'ordinate_desc' => __('Kbytes', 'rrze-statistik'),
                'headline_chart' => __('Kbytes (last 24 months)', 'rrze-statistik'),
                'tooltip_desc' => __(' Kbytes / Month', 'rrze-statistik'),
            ),
        );
        return $output;
    }

    public static function getLinechartDescription($type)
    {
        switch ($type) {
            case 'visits':
                return __('Line chart displaying the amount of site visitors during a time period of the last 23 months.', 'rrze-statistik');
            case 'hits':
                return __('Line chart displaying the number of hits during a time period of the last 23 months. A hit is a request for a file such as a web page, image, javascript, or CSS hosted on a web server.', 'rrze-statistik');
            case 'hosts':
                return __('Line chart displaying the number of hosts during a time period of the last 23 months. A host is a computer or server opening your website. Larger online services are often only displayed as a single host, if they share one server.', 'rrze-statistik');
            case 'files':
                return __('Line chart displaying the amount of successful loaded files from your domain. This includes all documents, css-files, scripts and media-files.', 'rrze-statistik');
            case 'kbytes':
                return __('Line chart displaying the amount of transferred Data in kBytes over a period of 23 months.', 'rrze-statistik');
        }
    }

    public static function getAccessibilityDescriptions($type)
    {
        switch ($type) {
            case 'visits':
                return __(' ', 'rrze-statistik');
            case 'hits':
                return __(' ', 'rrze-statistik');
            case 'hosts':
                return __(' ', 'rrze-statistik');
            case 'files':
                return __(' ', 'rrze-statistik');
            case 'kbytes':
                return __(' ', 'rrze-statistik');
        }
    }
}
