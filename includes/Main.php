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

        new Highcharts();
        Cron::init();
    }
}
