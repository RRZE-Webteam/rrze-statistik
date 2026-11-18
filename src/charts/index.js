import './style.scss';
import './ajax.js';
import {createChartModel, METRICS} from './model.js';
import {mountChart} from './widget.js';

const mounted = new Map();

/** Mount new widgets and dispose removed ones. @returns {void} */
function reconcileCharts() {
    for (const [root, dispose] of mounted) {
        if (!root.isConnected) {
            dispose();
            mounted.delete(root);
        }
    }
    const transfer = window.RRZESTATISTIKTRANSFER || {};
    for (const root of document.querySelectorAll('[data-statistik-chart]')) {
        if (mounted.has(root) || !METRICS.includes(root.dataset.metric)) continue;
        const model = createChartModel(transfer, root.dataset.metric);
        mounted.set(root, mountChart(root, model));
    }
}

/** Observe replacement of widget markup without duplicate instances. @returns {void} */
function startCharts() {
    reconcileCharts();
    const observer = new MutationObserver(reconcileCharts);
    observer.observe(document.body, {childList: true, subtree: true});
    /** Dispose observers and widgets on an actual page exit. @param {PageTransitionEvent} event Page event. @returns {void} */
    function disposeCharts(event) {
        if (event.persisted) return;
        observer.disconnect();
        for (const dispose of mounted.values()) dispose();
        mounted.clear();
    }
    window.addEventListener('pagehide', disposeCharts);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', startCharts, {once: true});
} else {
    startCharts();
}
