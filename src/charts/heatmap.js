import * as Plot from '@observablehq/plot';
import {formatValue, isFutureMonth} from './model.js';
import {monthTicks, svgElement} from './svg.js';

const BLUES = ['#c1dcf2', '#80b4dd', '#377fb7', '#164e80'];
const ZERO = '#f4f8fc';

/**
 * Draw the intensity key in the SVG so standalone images include the exact same scale.
 * @param {SVGElement} root Outer SVG.
 * @param {object} model Normalized chart model.
 * @param {object[]} entries Swatches with fill, outline and label.
 * @param {number} width Drawing width.
 * @param {number} top First available pixel below the matrix.
 * @param {boolean} hasScale Whether a positive value supplies numeric limits.
 * @returns {number} First available pixel after the key.
 */
function drawKey(root, model, entries, width, top, hasScale) {
    const key = svgElement('g', {class: 'rrze-statistik-heat-key', fill: '#344054', 'font-size': 12});
    let x = 8;
    let y = top + 14;
    const heading = hasScale ? model.labels.intensityScale + ' · ' + model.unit : model.unit;
    key.append(svgElement('text', {x, y, 'font-weight': 600}, heading));
    y += 20;
    for (const {fill, stroke, label} of entries) {
        const itemWidth = 31 + label.length * 7;
        if (x > 8 && x + itemWidth > width) {
            x = 8;
            y += 22;
        }
        key.append(svgElement('rect', {x, y: y - 10, width: 14, height: 11, rx: 2, fill, stroke}),
            svgElement('text', {x: x + 20, y}, label));
        x += itemWidth;
    }
    root.append(key);
    const desc = root.querySelector('desc');
    desc.textContent += ' ' + heading + ': ' + entries.map((entry) => entry.label).join(', ') + '.';
    return y + 10;
}

/**
 * Render one cell for each visible year/month using thresholds from the entire metric.
 * @param {SVGElement} root Destination SVG for both widget and export.
 * @param {object} model Normalized monthly measurements.
 * @param {object[]} series Visible years in chronological order.
 * @param {object} options Selected year and month, or export mode.
 * @param {number} width Available SVG width.
 * @param {number} top Vertical offset after the export heading.
 * @param {string} prefix Unique namespace for the missing-data hatch.
 * @returns {number} Height consumed by the matrix and its key.
 */
export function drawHeatmap(root, model, series, options, width, top, prefix) {
    const maximum = Math.max(0, ...model.series.flatMap((year) => year.values.filter((v) => v !== null)));
    const id = prefix + '-missing';
    const defs = svgElement('defs');
    const hatch = svgElement('pattern', {id, patternUnits: 'userSpaceOnUse', width: 6, height: 6});
    hatch.append(svgElement('rect', {width: 6, height: 6, fill: '#f4f6f8'}),
        svgElement('path', {d: 'M-1,1L1,-1 M0,6L6,0 M5,7L7,5', stroke: '#9aa8b6'}));
    defs.append(hatch);
    root.append(defs);

    const special = {
        zero: {fill: ZERO, stroke: '#78899a', label: formatValue(model, 0)},
        missing: {fill: 'url(#' + id + ')', stroke: '#96a5b4', label: model.labels.missingValue},
        future: {fill: '#fff', stroke: '#d6dde4', label: model.labels.futureMonth},
    };
    const bins = BLUES.map((fill, i) => {
        const limit = maximum * ((i + 1) / BLUES.length);
        return {fill, stroke: fill, limit, label: '≤ ' + formatValue(model, limit)};
    });
    const present = new Set();
    const cells = [];
    for (const {year, values} of series) {
        for (const [index, value] of values.entries()) {
            const month = index + 1;
            let style;
            if (value > 0) {
                style = bins.find((bin) => value <= bin.limit);
                present.add('positive');
            } else {
                const state = value === 0 ? 'zero' : isFutureMonth(model, year, month, value) ? 'future' : 'missing';
                style = special[state];
                present.add(state);
            }
            cells.push({year, month, fill: style.fill, stroke: style.stroke});
        }
    }
    const months = model.months.map((_, i) => i + 1);
    const height = 36 + series.length * Math.min(48, Math.max(26, (width - 52) / 12));
    const selected = cells.filter((cell) => cell.year === options.selectedYear && cell.month === options.selectedMonth);
    const geometry = {x: 'month', y: 'year', inset: 2, rx: 3};
    const marks = [Plot.cell(cells, {...geometry, fill: 'fill', stroke: 'stroke'})];
    if (selected.length && !options.exportMode) {
        marks.push(Plot.cell(selected, {...geometry, fill: 'none', stroke: '#fff', strokeWidth: 4}),
            Plot.cell(selected, {...geometry, fill: 'none', stroke: '#17283d', strokeWidth: 2,
                className: 'rrze-statistik-selection'}));
    }
    const plot = Plot.plot({
        document, width, height, marginLeft: 44, marginRight: 8, marginTop: 28, marginBottom: 8,
        style: {fontFamily: 'Arial, sans-serif', fontSize: '12px', color: '#344054'},
        x: {type: 'band', domain: months, axis: 'top', padding: 0, round: false, label: null,
            tickSize: 0, ticks: monthTicks(model.months, width - 52, options.selectedMonth),
            tickFormat: (month) => model.months[month - 1]},
        y: {type: 'band', domain: series.map((item) => item.year), padding: 0, round: false, label: null,
            tickSize: 0, tickFormat: String},
        color: {type: 'identity'}, marks,
    });
    plot.setAttribute('y', String(top));
    plot.setAttribute('aria-hidden', 'true');
    if (!options.exportMode) {
        const overlay = svgElement('g', {class: 'rrze-statistik-point-targets'});
        const x = plot.scale('x');
        const y = plot.scale('y');
        for (const {year, month} of cells) {
            overlay.append(svgElement('rect', {x: x.apply(month), y: y.apply(year), width: x.bandwidth,
                height: y.bandwidth, fill: 'transparent', 'data-year': year, 'data-month': month}));
        }
        plot.append(overlay);
    }
    root.append(plot);
    const entries = present.has('zero') ? [special.zero] : [];
    if (present.has('positive')) entries.push(...bins);
    for (const state of ['missing', 'future']) {
        if (present.has(state)) entries.push(special[state]);
    }
    return drawKey(root, model, entries, width, top + height + 5, present.has('positive')) - top;
}
