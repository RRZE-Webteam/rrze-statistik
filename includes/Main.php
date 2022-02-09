<?php

namespace RRZE\statistik;

defined('ABSPATH') || exit;

class Main
{
    /**
     * Full path- and file name of plugin.
     * @var string
     */
    protected $pluginFile;

    /**
     * Variablen Werte zuweisen.
     * @param string $pluginFile Path and file name of plugin
     */
    public function __construct($pluginFile)
    {
        $this->pluginFile = $pluginFile;
    }
    
    public function onLoaded()
    {
        new Helper();
        $shortcode = new Shortcode($this->pluginFile);
        $shortcode->onLoaded();
    }
}
