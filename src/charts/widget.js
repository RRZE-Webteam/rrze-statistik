import {createInitialState, describePoint, formatValue} from './model.js';
import {getSeriesStyle, renderChartSvg} from './render.js';
import {mountExportControls} from './exports.js';

const mountedCharts = new WeakMap();
let widgetSequence = 0;

/**
 * Create a safe HTML element with plain text content.
 *
 * @param {string} tag HTML element name.
 * @param {string} className Optional CSS class.
 * @param {string} text Optional text content.
 * @returns {HTMLElement} Detached element.
 */
function element(tag, className = '', text = '') {
    const node = document.createElement(tag);
    node.className = className;
    node.textContent = text;
    return node;
}

/**
 * Substitute named tokens in a translated message without HTML interpretation.
 *
 * @param {string} template Translated message.
 * @param {object} replacements Named plain text values.
 * @returns {string} Formatted message.
 */
function message(template, replacements) {
    return String(template).replace(/\{(\w+)\}/g, (match, key) => replacements[key] ?? match);
}

/**
 * Explain the range and extrema of the currently visible numeric values.
 *
 * @param {object} model Normalized chart model.
 * @param {number[]} visibleYears Active series.
 * @returns {string} Localized plain text summary.
 */
function summarize(model, visibleYears) {
    const points = [];
    for (const series of model.series) {
        if (!visibleYears.includes(series.year)) continue;
        series.values.forEach((value, index) => {
            if (value !== null) points.push({value, date: `${model.months[index]} ${series.year}`});
        });
    }
    if (!points.length) return model.labels.emptyChart;
    const min = points.reduce((lowest, point) => point.value < lowest.value ? point : lowest);
    const max = points.reduce((highest, point) => point.value > highest.value ? point : highest);
    return message(model.labels.summary, {
        range: message(model.labels.range, {first: model.years[0], last: model.years[model.years.length - 1]}),
        min: `${formatValue(model, min.value)} ${model.unit}`, minDate: min.date,
        max: `${formatValue(model, max.value)} ${model.unit}`, maxDate: max.date,
    });
}

/**
 * Populate a semantic month/year table from the same data used by CSV and Plot.
 *
 * @param {HTMLTableElement} table Table to replace.
 * @param {object} model Normalized chart model.
 * @param {number[]} visibleYears Included year columns.
 * @returns {void}
 */
function updateTable(table, model, visibleYears) {
    const caption = element('caption', '', `${message(model.labels.tableCaption, {title: model.title})} (${model.unit})`);
    const head = element('thead');
    const header = element('tr');
    const monthHeader = element('th', '', model.labels.month);
    monthHeader.scope = 'col';
    header.append(monthHeader);
    const series = model.series.filter((item) => visibleYears.includes(item.year));
    for (const item of series) {
        const cell = element('th', '', String(item.year));
        cell.scope = 'col';
        header.append(cell);
    }
    head.append(header);
    const body = element('tbody');
    for (let month = 0; month < 12; month += 1) {
        const row = element('tr');
        const label = element('th', '', model.months[month]);
        label.scope = 'row';
        row.append(label);
        for (const item of series) row.append(element('td', '', formatValue(model, item.values[month])));
        body.append(row);
    }
    table.replaceChildren(caption, head, body);
}

/**
 * Mount a responsive accessible chart, returning its explicit teardown function.
 *
 * @param {HTMLElement} root Existing WordPress chart placeholder.
 * @param {object} model Normalized chart model.
 * @returns {Function} Cleanup function for listeners, observers and export controls.
 */
export function mountChart(root, model) {
    mountedCharts.get(root)?.();
    const state = createInitialState(model);
    const id = `rrze-statistik-${model.metric}-${++widgetSequence}`;
    const controller = new AbortController();
    const eventOptions = {signal: controller.signal};
    let disposed = false;
    let scheduled = 0;
    let lastWidth = 0;

    const heading = element('h3', 'rrze-statistik-heading', model.title);
    heading.id = `${id}-heading`;
    const description = element('p', 'rrze-statistik-description', model.description);
    const summary = element('p', 'rrze-statistik-summary');
    summary.id = `${id}-summary`;
    const plotHost = element('div', 'rrze-statistik-plot');
    const legend = element('fieldset', 'rrze-statistik-legend');
    legend.append(element('legend', '', model.labels.visibleYears));
    const legendButtons = new Map();
    for (const year of model.years) {
        const button = element('button', 'button rrze-statistik-year-toggle');
        button.type = 'button';
        button.setAttribute('aria-label', `${model.labels.year} ${year}`);
        button.dataset.year = String(year);
        const style = getSeriesStyle(model, year);
        const swatch = element('span', `rrze-statistik-swatch rrze-statistik-swatch-${style.pattern}`);
        if (model.displayType === 'spline' || model.displayType === 'areaspline') {
            swatch.classList.add('rrze-statistik-swatch-line');
        }
        swatch.style.setProperty('--series-color', style.color);
        swatch.setAttribute('aria-hidden', 'true');
        button.append(swatch, document.createTextNode(String(year)));
        legend.append(button);
        legendButtons.set(year, button);
    }

    const controls = element('div', 'rrze-statistik-explorer');
    const help = element('p', 'rrze-statistik-navigation-help rrze-statistik-sr-only', model.labels.navigationHelp);
    help.id = `${id}-help`;
    const controlHeader = element('div', 'rrze-statistik-explorer-header');
    const yearField = element('div', 'rrze-statistik-field');
    const yearLabel = element('label', '', model.labels.year);
    const selectWrapper = element('div', 'rrze-statistik-select-wrapper');
    const yearSelect = element('select', 'rrze-statistik-year-select');
    yearSelect.id = `${id}-year`;
    yearSelect.setAttribute('aria-label', model.labels.chooseYear);
    yearLabel.htmlFor = yearSelect.id;
    selectWrapper.append(yearSelect);
    yearField.append(yearLabel, selectWrapper);
    const monthField = element('div', 'rrze-statistik-field rrze-statistik-month-field');
    const monthLabel = element('label', '', model.labels.month);
    const slider = element('input', 'rrze-statistik-month-slider');
    slider.type = 'range';
    slider.min = '1';
    slider.max = '12';
    slider.step = '1';
    slider.id = `${id}-month`;
    slider.setAttribute('aria-label', model.labels.chooseMonth);
    slider.setAttribute('aria-describedby', help.id);
    monthLabel.htmlFor = slider.id;
    const monthScale = element('div', 'rrze-statistik-month-scale');
    monthScale.setAttribute('aria-hidden', 'true');
    model.months.forEach((month) => monthScale.append(element('span', 'rrze-statistik-month-tick', month)));
    monthField.append(monthLabel, slider, monthScale);
    const output = element('output', 'rrze-statistik-current-value');
    output.setAttribute('for', `${yearSelect.id} ${slider.id}`);
    output.setAttribute('aria-live', 'off');
    const outputDescription = element('span', 'rrze-statistik-sr-only');
    const outputVisual = element('span', 'rrze-statistik-value-visual');
    outputVisual.setAttribute('aria-hidden', 'true');
    const selectedDate = element('span', 'rrze-statistik-selected-date');
    const selectedValue = element('span', 'rrze-statistik-value-number');
    const selectedUnit = element('span', 'rrze-statistik-value-unit');
    outputVisual.append(selectedDate, selectedValue, selectedUnit);
    output.append(outputDescription, outputVisual);
    controlHeader.append(yearField, output);
    controls.append(controlHeader, monthField);

    const details = element('details', 'rrze-statistik-data');
    details.append(element('summary', '', model.labels.showTable));
    const tableWrapper = element('div', 'rrze-statistik-table-scroll');
    tableWrapper.tabIndex = 0;
    tableWrapper.setAttribute('role', 'region');
    tableWrapper.setAttribute('aria-label', message(model.labels.tableCaption, {title: model.title}));
    const table = element('table', 'rrze-statistik-data-table');
    tableWrapper.append(table);
    details.append(tableWrapper);
    const toolbar = element('div', 'rrze-statistik-actions');
    const status = element('p', 'rrze-statistik-status screen-reader-text');
    status.setAttribute('role', 'status');
    status.setAttribute('aria-live', 'polite');
    const source = element('p', 'rrze-statistik-source');
    try {
        const url = new URL(model.sourceUrl);
        if (url.protocol === 'https:' || url.protocol === 'http:') {
            const link = element('a', '', model.sourceText);
            link.href = url.href;
            source.append(link);
        } else {
            source.textContent = model.sourceText;
        }
    } catch {
        source.textContent = model.sourceText;
    }
    root.replaceChildren(heading, description, summary, plotHost, legend, help, controls, details, toolbar, status);
    if (!root.parentElement?.querySelector('.rrze-statistik-source')) root.append(source);

    /**
     * Measure widget width while permitting hidden widgets to initialize safely.
     * @returns {number} Drawing width in CSS pixels.
     */
    function currentWidth() {
        return Math.max(160, Math.floor(root.clientWidth || root.getBoundingClientRect().width || 480));
    }

    /**
     * Redraw SVG only, preserving native controls, focus and the open data table.
     * @returns {void}
     */
    function updatePlot() {
        if (disposed) return;
        lastWidth = currentWidth();
        try {
            const svg = renderChartSvg(model, state.visibleYears, {
                width: lastWidth, selectedYear: state.selectedYear, selectedMonth: state.selectedMonth,
            });
            svg.setAttribute('aria-labelledby', heading.id);
            svg.setAttribute('aria-describedby', summary.id);
            plotHost.replaceChildren(svg);
        } catch (error) {
            plotHost.replaceChildren(element('p', 'notice notice-error rrze-statistik-render-error', model.labels.renderError));
            console.error('RRZE Statistik: chart rendering failed.', error);
        }
    }

    /**
     * Synchronize native selection controls and their accessible value.
     * @param {boolean} announce Whether to announce a year-change value.
     * @returns {void}
     */
    function updateSelection(announce = false) {
        yearSelect.value = String(state.selectedYear);
        slider.value = String(state.selectedMonth);
        const value = describePoint(model, state.selectedYear, state.selectedMonth);
        slider.setAttribute('aria-valuetext', value);
        slider.style.setProperty('--month-progress', `${(state.selectedMonth - 1) / 11 * 100}%`);
        const measurement = model.series.find((series) => series.year === state.selectedYear)
            ?.values[state.selectedMonth - 1] ?? null;
        outputDescription.textContent = value;
        selectedDate.textContent = new Intl.DateTimeFormat(model.locale, {
            month: 'long', year: 'numeric', timeZone: 'UTC',
        }).format(new Date(Date.UTC(state.selectedYear, state.selectedMonth - 1, 1)));
        selectedValue.textContent = formatValue(model, measurement);
        selectedUnit.textContent = measurement === null ? '' : model.unit;
        output.classList.toggle('rrze-statistik-value-missing', measurement === null);
        const color = getSeriesStyle(model, state.selectedYear).color;
        controls.style.setProperty('--explorer-accent', color);
        if (announce) status.textContent = value;
        updatePlot();
    }

    /**
     * Refresh views whose contents depend on series visibility.
     * @returns {void}
     */
    function updateVisibleYears() {
        const options = state.visibleYears.map((year) => {
            const option = element('option', '', String(year));
            option.value = String(year);
            return option;
        });
        yearSelect.replaceChildren(...options);
        for (const [year, button] of legendButtons) {
            const visible = state.visibleYears.includes(year);
            button.setAttribute('aria-pressed', String(visible));
            button.setAttribute('aria-disabled', String(visible && state.visibleYears.length === 1));
        }
        summary.textContent = summarize(model, state.visibleYears);
        updateTable(table, model, state.visibleYears);
        updateSelection();
    }

    /**
     * Toggle a series without permitting an unusable empty set of visible years.
     * @param {MouseEvent} event Native button activation.
     * @returns {void}
     */
    function toggleYear(event) {
        const button = event.target.closest('button[data-year]');
        if (!button || !legend.contains(button)) return;
        const year = Number(button.dataset.year);
        if (state.visibleYears.includes(year)) {
            if (state.visibleYears.length === 1) {
                status.textContent = model.labels.lastVisibleYear;
                return;
            }
            state.visibleYears = state.visibleYears.filter((value) => value !== year);
        } else {
            state.visibleYears = model.years.filter((value) => value === year || state.visibleYears.includes(value));
        }
        if (!state.visibleYears.includes(state.selectedYear)) {
            state.selectedYear = state.visibleYears[state.visibleYears.length - 1];
        }
        updateVisibleYears();
        status.textContent = `${model.labels.visibleYears}: ${state.visibleYears.join(', ')}.`;
    }

    /**
     * Update the selected year from its native select control.
     * @returns {void}
     */
    function selectYear() {
        state.selectedYear = Number(yearSelect.value);
        updateSelection(true);
    }

    /**
     * Update the selected month without duplicating the slider's spoken value.
     * @returns {void}
     */
    function selectMonth() {
        state.selectedMonth = Number(slider.value);
        updateSelection();
    }

    /**
     * Mirror point selection by mouse or touch into the native exploration UI.
     * @param {PointerEvent} event Pointer event on a plot target.
     * @returns {void}
     */
    function selectPoint(event) {
        const target = event.target.closest('[data-year][data-month]');
        if (!target || !plotHost.contains(target)) return;
        const year = Number(target.dataset.year);
        const month = Number(target.dataset.month);
        if (state.selectedYear === year && state.selectedMonth === month) return;
        state.selectedYear = year;
        state.selectedMonth = month;
        updateSelection();
    }

    /**
     * Coalesce responsive redraws without disturbing the active native control.
     * @returns {void}
     */
    function handleResize() {
        if (disposed || scheduled || currentWidth() === lastWidth) return;
        scheduled = requestAnimationFrame(() => {
            scheduled = 0;
            updatePlot();
        });
    }

    legend.addEventListener('click', toggleYear, eventOptions);
    yearSelect.addEventListener('change', selectYear, eventOptions);
    slider.addEventListener('input', selectMonth, eventOptions);
    plotHost.addEventListener('pointermove', selectPoint, eventOptions);
    plotHost.addEventListener('pointerdown', selectPoint, eventOptions);
    updateVisibleYears();
    const stopExports = mountExportControls(toolbar, model, () => state.visibleYears.slice());
    const observer = typeof ResizeObserver === 'function' ? new ResizeObserver(handleResize) : null;
    observer?.observe(root);
    if (!observer) window.addEventListener('resize', handleResize, eventOptions);

    /**
     * Release every resource created by this widget before a remount/removal.
     * @returns {void}
     */
    function dispose() {
        disposed = true;
        controller.abort();
        observer?.disconnect();
        if (scheduled) cancelAnimationFrame(scheduled);
        stopExports?.();
        mountedCharts.delete(root);
    }
    mountedCharts.set(root, dispose);
    return dispose;
}
