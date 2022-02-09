<?php

namespace RRZE\statistik;

defined('ABSPATH') || exit;

/**
 * Shortcode
 */
class Shortcode
{
    protected $pluginFile;
    
    public function __construct($pluginFile)
    {
        $this->pluginFile = $pluginFile;
    }

    public function onLoaded()
    {
        add_shortcode('rrze_statistik', [$this, 'shortcodeOutput'], 10, 2);
    }

    public function shortcodeOutput($atts)
    {
        $html = $this->getContent();
        return $html;
    }

    public function getContent()
    {
        return '<p>Testoutput RRZE Statistik</p>';
    }
}