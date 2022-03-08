<?php

namespace RRZE\Statistik;

defined('ABSPATH') || exit;

class Shortcode
{
    public function onLoaded()
    {
        add_shortcode('rrze_statistik', [$this, 'shortcodeOutput'], 10, 2);
    }

    public function shortcodeOutput($atts)
    {
        $analytics = new Analytics();
        return $analytics->getLinechart('visits');
    }
}
