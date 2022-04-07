<?php

namespace RRZE\Statistik;

defined('ABSPATH') || exit;

class Main
{
    public function __construct()
    {
        new Helper();
        new Dashboard();
        new Data();

        $highcharts = new Highcharts();
        $highcharts->loadHighcharts();
        Cron::init();
    }
}
