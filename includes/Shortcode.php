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
        Analytics::getUrlDatasetTable();
        return $analytics->getLinechart('visits').$analytics->getUrlDatasetTable();
        
        /*
        foreach($data as $value){
            if((int)$value['recorded_days']>= 30){
                var_dump($value['recorded_days']);   
            } elseif ( (int)$value['recorded_days']>=27 && $value['month'] === '2'){
                var_dump($value['recorded_days']);
            }
        }
        */
        
        //var_dump(Experimente::getDummyData());
    }
}
