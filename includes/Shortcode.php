<?php

namespace RRZE\Statistik;

defined('ABSPATH') || exit;

/**
 * Shortcode
 */
class Shortcode
{
    public function __construct()
    {
        $this->onLoaded();
    }

    public function onLoaded()
    {
        add_shortcode('rrze_statistik', [$this, 'shortcodeOutput'], 10, 2);
    }

    public function shortcodeOutput($atts)
    {
        Data::updateDataWeekly();
        /*
        $data = get_option('rrze_statistik_url_dataset');
        return Analytics::getUrlDatasetTable();*/
    }
}