<?php

namespace RRZE\statistik;

defined('ABSPATH') || exit;

/**
 * Shortcode
 */
class Shortcode
{
    public $plugin_basename;

    public function __construct($plugin_basename)
    {
        $this->plugin_basename = $plugin_basename;
    }



    public function onLoaded()
    {
        add_shortcode('rrze_statistik', [$this, 'shortcodeOutput'], 10, 2);
    }

    public function shortcodeOutput($atts)
    {

        return  'Weshalb funktioniert das Einbinden des JS-Files nicht';
    }

    public function getContent()
    {
        return '<p>Testoutput RRZE Statistik</p>';
    }
}