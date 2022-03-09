<?php

namespace RRZE\Statistik;

defined('ABSPATH') || exit;

class Cron
{
    const ACTION_HOOK = 'rrze_statistik_schedule_event';
    const ACTION_HOOK_WEEKLY = 'rrze_statistik_schedule_event_weekly';

    public static function init()
    {
        add_action(self::ACTION_HOOK, [__CLASS__, 'runEvents']);
        add_action('init', [__CLASS__, 'activateScheduledEvents']);

        add_action(self::ACTION_HOOK_WEEKLY, [__CLASS__, 'runEventsWeekly']);
        add_action('init', [__CLASS__, 'activateScheduledEventsWeekly']);
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
        Data::updateData();
    }

        /**
     * activateScheduledEventsWeekly
     * Activate all scheduled events.
     */
    public static function activateScheduledEventsWeekly()
    {
        if (false === wp_next_scheduled(self::ACTION_HOOK_WEEKLY)) {
            wp_schedule_event(
                time(),
                'weekly',
                self::ACTION_HOOK_WEEKLY,
                [],
                true
            );
        }
    }

    /**
     * runEventsWeekly
     * Run the scheduled events.
     */
    public static function runEventsWeekly()
    {
        Data::updateDataWeekly();
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
