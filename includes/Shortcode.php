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
        $shortcode_attr = shortcode_atts(array(
            'url'           => 'www.wordpress.rrze.fau.de',
        ), $atts);

        $url = $shortcode_attr['url'];

        $analytics = new Analytics();
        return $analytics->getLinechart($url);
    }
}
