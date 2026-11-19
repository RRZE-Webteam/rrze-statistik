<?php

namespace RRZE\Statistik;

defined('ABSPATH') || exit;

class Main
{
    /**
     * Initializes the dashboard, data providers and shared chart asset hooks.
     */
    public function __construct()
    {
        new Helper();
        new Dashboard();
        new Data();

        new Charts();
        Cron::init();
    }
}
