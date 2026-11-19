<?php

namespace RRZE\Statistik;

defined('ABSPATH') || exit;

/** Migrates WordPress's user-wide dashboard layout to the consolidated widget. */
class DashboardLayout
{
    const WIDGET_ID = 'rrze_statistik_widget_visits';
    const MIGRATION_KEY = 'rrze_statistik_dashboard_layout_version';
    const VERSION = '1';

    /**
     * Consolidates legacy widget IDs once for the current user, across all sites.
     * WordPress stores these layout keys without a site prefix. Chart preferences
     * are separate site-specific user options and are not modified here.
     * @return void
     */
    public static function migrate()
    {
        $user = get_current_user_id();
        if (!$user || get_user_meta($user, self::MIGRATION_KEY, true) === self::VERSION) return;

        $legacy = get_option('rrze_statistik_widget');
        $view = is_array($legacy) ? ($legacy['data_type'] ?? 'visits_and_sites') : 'visits_and_sites';
        $views = $view === 'display_all' ? Dashboard::VIEWS
            : (in_array($view, Dashboard::VIEWS, true) ? [$view] : ['visits', 'urls']);
        $allIds = [];
        foreach (Dashboard::VIEWS as $metric) $allIds[] = 'rrze_statistik_widget_' . $metric;
        $offeredIds = [];
        foreach ($views as $metric) $offeredIds[] = 'rrze_statistik_widget_' . $metric;

        $original = [];
        foreach (['metaboxhidden_dashboard', 'closedpostboxes_dashboard', 'meta-box-order_dashboard'] as $key) {
            $original[$key] = get_user_meta($user, $key, true);
        }
        $hidden = is_array($original['metaboxhidden_dashboard']) ? $original['metaboxhidden_dashboard'] : [];
        $closed = is_array($original['closedpostboxes_dashboard']) ? $original['closedpostboxes_dashboard'] : [];
        $order = is_array($original['meta-box-order_dashboard']) ? $original['meta-box-order_dashboard'] : [];
        $visibleIds = array_values(array_diff($offeredIds, $hidden));
        $orderedIds = [];
        foreach ($order as $ids) {
            if (!is_string($ids)) continue;
            foreach (explode(',', $ids) as $id) {
                if (in_array($id, $offeredIds, true) && !in_array($id, $orderedIds, true)) $orderedIds[] = $id;
            }
        }
        // Unsorted widgets follow the explicitly ordered ones, like WordPress.
        $candidates = array_values(array_unique(array_merge($orderedIds, $offeredIds)));
        $visibleCandidates = array_values(array_intersect($candidates, $visibleIds));
        $anchor = $visibleCandidates[0] ?? $candidates[0];

        $nextHidden = array_values(array_diff($hidden, $allIds));
        if (!$visibleIds) $nextHidden[] = self::WIDGET_ID;
        $nextClosed = array_values(array_diff($closed, $allIds));
        if (in_array($anchor, $closed, true)) $nextClosed[] = self::WIDGET_ID;
        $nextOrder = $order;
        $inserted = false;
        foreach ($order as $context => $ids) {
            if (!is_string($ids)) continue;
            $nextIds = [];
            foreach (explode(',', $ids) as $id) {
                if ($id === $anchor && !$inserted) {
                    $nextIds[] = self::WIDGET_ID;
                    $inserted = true;
                } elseif (!in_array($id, $allIds, true)) {
                    $nextIds[] = $id;
                }
            }
            $nextOrder[$context] = implode(',', $nextIds);
        }

        $changes = [
            'metaboxhidden_dashboard' => $nextHidden,
            'closedpostboxes_dashboard' => $nextClosed,
            'meta-box-order_dashboard' => $nextOrder,
        ];
        $written = [];
        foreach ($changes as $key => $value) {
            $before = is_array($original[$key]) ? $original[$key] : [];
            if ($value === $before) continue;
            update_user_meta($user, $key, $value);
            if (get_user_meta($user, $key, true) !== $value) {
                // Keep a failed attempt from leaving half of the layout migrated.
                foreach ($written as $writtenKey) update_user_meta($user, $writtenKey, $original[$writtenKey]);
                return;
            }
            $written[] = $key;
        }
        update_user_meta($user, self::MIGRATION_KEY, self::VERSION);
        if (get_user_meta($user, self::MIGRATION_KEY, true) !== self::VERSION) {
            foreach ($written as $key) update_user_meta($user, $key, $original[$key]);
        }
    }
}
