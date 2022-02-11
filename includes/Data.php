<?php

namespace RRZE\statistik;

defined('ABSPATH') || exit;

/**
 * Collects data from statistiken.rrze.fau.de
 */
class Data
{
    public function __construct($plugin_basename)
    {
        $this->plugin_basename = $plugin_basename;
    }
}