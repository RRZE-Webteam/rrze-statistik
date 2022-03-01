<?php

namespace RRZE\Statistik;

defined('ABSPATH') || exit;

class Cron
{
    const ACTION_HOOK = 'rrze_statistik_schedule_event';

    public static function init()
    {
        add_action(self::ACTION_HOOK, [__CLASS__, 'runEvents']);
        add_action('init', [__CLASS__, 'activateScheduledEvents']);
    }

    /**
     * activateScheduledEvents
     * Activate all scheduled events.
     */
    public static function activateScheduledEvents()
    {
        if (false === wp_next_scheduled(self::ACTION_HOOK)) {
            wp_schedule_event(
                time(),
                'hourly',
                self::ACTION_HOOK,
                [],
                true
            );
        }
    }

    /**
     * runEvents
     * Run the scheduled events.
     */
    public static function runEvents()
    {
        $data = new Data;
        Data::updateData();
    }

    /**
     * clearSchedule
     * Clear all scheduled events.
     */
    public static function clearSchedule()
    {
        wp_clear_scheduled_hook(self::ACTION_HOOK);
    }
}
