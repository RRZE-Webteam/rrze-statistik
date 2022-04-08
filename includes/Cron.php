<?php

namespace RRZE\Statistik;

defined('ABSPATH') || exit;

class Cron
{
    const ACTION_HOOK = 'rrze_statistik_schedule_event';
    const ACTION_HOOK_WEEKLY = 'rrze_statistik_schedule_event_weekly';

    public static function init()
    {
        add_action( 'init', [__CLASS__, 'clearSchedule'] ); 
    }

    /**
     * clearSchedule
     * Clear all scheduled events.
     */
    public static function clearSchedule()
    {
        wp_clear_scheduled_hook(self::ACTION_HOOK);
        wp_clear_scheduled_hook(self::ACTION_HOOK_WEEKLY);
    }
}
