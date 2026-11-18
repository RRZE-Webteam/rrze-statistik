import {createChartModel, DEFAULT_LABELS, DISPLAY_TYPES, METRICS} from './model.js';
import {mountChart} from './widget.js';

/**
 * Enhance server-rendered statistics with accessible tabs and personal preferences.
 * Charts mount on first activation and retain their exploration state between tabs.
 * @param {HTMLElement} root Dashboard wrapper with tabs and panels.
 * @param {object} transfer WordPress data and localized descriptions.
 * @returns {() => void} Cleanup for charts, listeners and pending preference requests.
 */
export function mountDashboard(root, transfer = {}) {
    const controller = new AbortController();
    const eventOptions = {signal: controller.signal};
    const tabs = [...root.querySelectorAll('[data-statistik-tab]')];
    const panels = new Map([...root.querySelectorAll('[data-statistik-panel]')]
        .map((panel) => [panel.dataset.statistikPanel, panel]));
    const charts = new Map();
    const controls = root.querySelector('.rrze-statistik-dashboard-controls');
    const typeField = root.querySelector('.rrze-statistik-display-controls');
    const typeSelect = root.querySelector('[data-statistik-display-type]');
    const feedback = root.querySelector('.rrze-statistik-preferences-feedback');
    const status = root.querySelector('.rrze-statistik-preferences-status');
    const progress = root.querySelector('.rrze-statistik-preferences-progress');
    const retry = root.querySelector('[data-statistik-retry]');
    let view = panels.has(transfer.preferences?.view) ? transfer.preferences.view : 'visits';
    let displayType = transfer.preferences?.displayType || transfer.displayType;
    if (!DISPLAY_TYPES.includes(displayType)) displayType = 'spline';
    let pendingPreferences = null;
    let saving = false;
    let lastSaved = {view, displayType};
    let saveTimer = 0;
    let progressTimer = 0;
    let clearProgressTimer = 0;

    /**
     * Read a description matching the currently selected representation.
     * @param {string} metric Metric key.
     * @returns {string} Localized metric description.
     */
    function descriptionFor(metric) {
        return transfer.descriptionsByType?.[displayType]?.[metric] || transfer.descriptions?.[metric] || '';
    }

    /**
     * Compare only the two persisted fields, ignoring object identity.
     * @param {object|null} first Confirmed or queued selection.
     * @param {object|null} second Selection to compare.
     * @returns {boolean} Whether both represent the same saved preference.
     */
    function samePreferences(first, second) {
        return Boolean(first && second && first.view === second.view && first.displayType === second.displayType);
    }

    /**
     * Coalesce short bursts of input and skip selections already confirmed by WordPress.
     * @param {boolean} immediate Whether an explicit retry should bypass the debounce.
     * @returns {void}
     */
    function scheduleSave(immediate = false) {
        if (!transfer.ajaxurl || !transfer.preferencesNonce || controller.signal.aborted) return;
        pendingPreferences = {view, displayType};
        clearTimeout(saveTimer);
        clearTimeout(clearProgressTimer);
        if (saving) return;
        if (samePreferences(pendingPreferences, lastSaved)) {
            pendingPreferences = null;
            setProgress('');
            feedback.hidden = true;
            return;
        }
        setProgress('');
        if (immediate) void savePreferences();
        else saveTimer = setTimeout(savePreferences, 250);
    }

    /**
     * Serialize writes while sending only the latest pending selection.
     * Busy feedback is delayed to avoid flashing on fast saves; success expires.
     * @returns {Promise<void>} Completion after the latest selection is confirmed or rejected.
     */
    async function savePreferences() {
        saveTimer = 0;
        if (saving || !pendingPreferences || controller.signal.aborted) return;
        saving = true;
        feedback.hidden = true;
        progressTimer = setTimeout(showSaving, 500);
        let failed = false;
        while (pendingPreferences && !controller.signal.aborted) {
            const preferences = pendingPreferences;
            pendingPreferences = null;
            if (samePreferences(preferences, lastSaved)) continue;
            try {
                const response = await fetch(transfer.ajaxurl, {
                    method: 'POST', credentials: 'same-origin', signal: controller.signal,
                    body: new URLSearchParams({
                        action: 'rrze_statistik_save_preferences', nonce: transfer.preferencesNonce,
                        ...preferences,
                    }),
                });
                if (!response.ok || !(await response.json()).success) throw new Error('Preference save failed.');
                lastSaved = preferences;
                feedback.hidden = true;
                status.textContent = '';
                failed = false;
            } catch (error) {
                if (controller.signal.aborted) break;
                // A lost response does not prove that the server rejected the write.
                lastSaved = null;
                if (!pendingPreferences) {
                    failed = true;
                    feedback.hidden = false;
                    status.textContent = transfer.labels?.preferencesError || DEFAULT_LABELS.preferencesError;
                }
            }
        }
        saving = false;
        clearTimeout(progressTimer);
        setProgress(failed || controller.signal.aborted ? ''
            : transfer.labels?.preferencesSaved || DEFAULT_LABELS.preferencesSaved);
        if (!failed && !controller.signal.aborted) clearProgressTimer = setTimeout(clearProgress, 1500);
    }

    /**
     * Keep saving announcements accessible while showing only a compact busy indicator.
     * @param {string} text Localized progress or confirmation message.
     * @param {boolean} busy Whether the request is pending.
     * @returns {void}
     */
    function setProgress(text, busy = false) {
        progress.classList.toggle('is-saving', busy);
        const label = document.createElement('span');
        label.className = 'rrze-statistik-progress-label';
        label.textContent = text;
        progress.replaceChildren(label);
        if (busy) {
            const indicator = document.createElement('span');
            indicator.className = 'rrze-statistik-save-indicator';
            indicator.setAttribute('aria-hidden', 'true');
            progress.append(indicator);
        }
    }

    /** Announce a save only when its request is still pending. @returns {void} */
    function showSaving() {
        setProgress(transfer.labels?.preferencesSaving || DEFAULT_LABELS.preferencesSaving, true);
    }

    /** Remove the short-lived success confirmation. @returns {void} */
    function clearProgress() {
        setProgress('');
    }

    /**
     * Activate a tab without moving focus into or rebuilding its retained chart.
     * @param {string} nextView Requested tab key.
     * @param {boolean} focus Whether keyboard navigation should focus its tab.
     * @param {boolean} persist Whether this user action should be saved.
     * @returns {void}
     */
    function activate(nextView, focus = false, persist = false) {
        if (!panels.has(nextView)) return;
        view = nextView;
        for (const tab of tabs) {
            const selected = tab.dataset.statistikTab === view;
            tab.setAttribute('aria-selected', String(selected));
            tab.tabIndex = selected ? 0 : -1;
            if (selected && focus) tab.focus();
        }
        for (const [key, panel] of panels) panel.hidden = key !== view;
        typeField.hidden = view === 'urls';
        if (METRICS.includes(view)) {
            if (!charts.has(view)) {
                const chartRoot = panels.get(view).querySelector('[data-statistik-chart]');
                const model = createChartModel({...transfer, displayType}, view);
                model.description = descriptionFor(view);
                charts.set(view, mountChart(chartRoot, model));
            } else {
                charts.get(view).setDisplayType(displayType, descriptionFor(view));
            }
        }
        if (persist) scheduleSave();
    }

    /**
     * Handle pointer or native Enter/Space activation of a tab button.
     * @param {MouseEvent} event Tab-list click.
     * @returns {void}
     */
    function selectTab(event) {
        const tab = event.target.closest('[data-statistik-tab]');
        if (tab && tabs.includes(tab)) activate(tab.dataset.statistikTab, false, true);
    }

    /**
     * Apply the horizontal tabs keyboard pattern with automatic local activation.
     * @param {KeyboardEvent} event Key pressed on a tab.
     * @returns {void}
     */
    function navigateTabs(event) {
        const index = tabs.indexOf(event.target);
        if (index < 0) return;
        let next;
        switch (event.key) {
            case 'ArrowRight': next = (index + 1) % tabs.length; break;
            case 'ArrowLeft': next = (index + tabs.length - 1) % tabs.length; break;
            case 'Home': next = 0; break;
            case 'End': next = tabs.length - 1; break;
            default: return;
        }
        event.preventDefault();
        activate(tabs[next].dataset.statistikTab, true, true);
    }

    /**
     * Update all visited charts so subsequent exports use the current representation.
     * @returns {void}
     */
    function selectDisplayType() {
        if (!DISPLAY_TYPES.includes(typeSelect.value)) return;
        displayType = typeSelect.value;
        for (const [metric, chart] of charts) chart.setDisplayType(displayType, descriptionFor(metric));
        scheduleSave();
    }

    /** Retry the latest local selection after a save failure. @returns {void} */
    function retrySaving() {
        tabs.find((tab) => tab.dataset.statistikTab === view).focus();
        scheduleSave(true);
    }

    for (const [key, panel] of panels) {
        panel.setAttribute('role', 'tabpanel');
        panel.setAttribute('aria-labelledby', tabs.find((tab) => tab.dataset.statistikTab === key).id);
        panel.tabIndex = 0;
    }
    const tablist = root.querySelector('[role="tablist"]');
    tablist.addEventListener('click', selectTab, eventOptions);
    tablist.addEventListener('keydown', navigateTabs, eventOptions);
    typeSelect.addEventListener('change', selectDisplayType, eventOptions);
    retry.addEventListener('click', retrySaving, eventOptions);
    typeSelect.value = displayType;
    activate(view);
    controls.hidden = false;

    /** Release charts and pending requests when WordPress removes the widget. @returns {void} */
    function dispose() {
        clearTimeout(saveTimer);
        clearTimeout(progressTimer);
        clearTimeout(clearProgressTimer);
        controller.abort();
        for (const chart of charts.values()) chart.dispose();
        charts.clear();
    }
    return dispose;
}
