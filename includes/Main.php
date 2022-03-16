<?php

namespace RRZE\Statistik;

defined('ABSPATH') || exit;

class Main
{
    public function __construct()
    {
        new Helper();
        $highcharts = new Highcharts();
        $highcharts->loadHighcharts();

        new Dashboard();

        Cron::init();
    }
}
