import * as Plot from '@observablehq/plot';
import {formatVisibleYears} from './model.js';
import {svgElement, monthTicks} from './svg.js';
import {drawHeatmap} from './heatmap.js';

const SERIES_STYLES = [
    {color: '#005a9c', dash: '', symbol: 'circle', pattern: 'solid'},
    // The stroke-oriented diamond has equal width and height, avoiding the
    // elongated silhouette of the fill-oriented diamond at small marker sizes.
    {color: '#8c4a00', dash: '8,4', symbol: 'diamond2', pattern: 'diagonal'},
    {color: '#6b3fa0', dash: '2,4', symbol: 'square', pattern: 'dots'},
];
let renderSequence = 0;

/**
 * Return a stable visual identity, including a non-color distinction, for a year.
 *
 * @param {object} model Normalized chart model.
 * @param {number} year Calendar year.
 * @returns {{color:string, dash:string, symbol:string, pattern:string}} Series style.
 */
export function getSeriesStyle(model, year) {
    return SERIES_STYLES[Math.max(0, model.years.indexOf(year)) % SERIES_STYLES.length];
}

/**
 * Split labels into bounded lines, including long unbroken source URLs.
 *
 * @param {string} text Plain label.
 * @param {number} maxCharacters Approximate maximum line length.
 * @returns {string[]} Wrapped lines.
 */
function wrapText(text, maxCharacters) {
    const chunks = [];
    for (const word of String(text).split(/\s+/)) {
        for (let offset = 0; offset < word.length; offset += maxCharacters) {
            chunks.push(word.slice(offset, offset + maxCharacters));
        }
    }
    const lines = [''];
    for (const chunk of chunks) {
        const last = lines.length - 1;
        if (lines[last] && lines[last].length + chunk.length + 1 > maxCharacters) {
            lines.push(chunk);
        } else {
            lines[last] += `${lines[last] ? ' ' : ''}${chunk}`;
        }
    }
    return lines;
}

/**
 * Append portable SVG text lines and return the following baseline position.
 *
 * @param {SVGElement} parent Destination SVG element.
 * @param {string} text Text to wrap.
 * @param {number} width Available drawing width.
 * @param {number} baseline First text baseline.
 * @param {number} fontSize Font size in CSS pixels.
 * @param {string} weight Font weight.
 * @returns {number} Baseline position after the final line.
 */
function appendWrappedText(parent, text, width, baseline, fontSize = 12, weight = 'normal') {
    const lines = wrapText(text, Math.max(12, Math.floor((width - 40) / (fontSize * 0.62))));
    for (const line of lines) {
        parent.append(svgElement('text', {
            x: 20, y: baseline, fill: '#1d2327', 'font-size': fontSize, 'font-weight': weight,
        }, line));
        baseline += fontSize + 6;
    }
    return baseline;
}

/**
 * Make three differently textured fills that remain identifiable without color.
 *
 * @param {SVGElement} root SVG receiving its own definitions.
 * @param {object} model Normalized chart model.
 * @param {string} prefix Unique namespace for SVG definition IDs.
 * @returns {Map<number,string>} Fill URL or color indexed by year.
 */
function appendPatterns(root, model, prefix) {
    const defs = svgElement('defs');
    const fills = new Map();
    for (const year of model.years) {
        const style = getSeriesStyle(model, year);
        if (style.pattern === 'solid') {
            fills.set(year, style.color);
            continue;
        }
        const id = `${prefix}-${year}`;
        const pattern = svgElement('pattern', {id, width: 8, height: 8, patternUnits: 'userSpaceOnUse'});
        pattern.append(svgElement('rect', {width: 8, height: 8, fill: style.color}));
        if (style.pattern === 'diagonal') {
            pattern.append(svgElement('path', {
                d: 'M-2,2L2,-2M0,8L8,0M6,10L10,6', stroke: '#fff', 'stroke-width': 1.2, 'stroke-opacity': 0.48,
            }));
        } else {
            pattern.append(svgElement('circle', {cx: 4, cy: 4, r: 1.2, fill: '#fff', 'fill-opacity': 0.65}));
        }
        defs.append(pattern);
        fills.set(year, `url(#${id})`);
    }
    root.append(defs);
    return fills;
}

/**
 * Preserve explicit gaps while creating plot records for a single year.
 *
 * @param {{year:number,values:Array<number|null>}} series Source series.
 * @returns {Array<{year:number,month:number,value:number|null,segment:number}>} Plot records.
 */
function seriesRecords(series) {
    let segment = 0;
    return series.values.map((value, index) => {
        if (value === null) segment += 1;
        return {year: series.year, month: index + 1, value, segment};
    });
}

/**
 * Compute the grouped interval occupied by a year within a month.
 *
 * @param {number} month One-based month index.
 * @param {number} slot Position among visible years.
 * @param {number} count Number of visible years.
 * @param {number} pitch Width or height of one month in pixels.
 * @returns {[number,number]} Bounds on the month scale.
 */
function groupBounds(month, slot, count, pitch) {
    const innerGap = Math.min(2, pitch * 0.04);
    const bar = Math.min(28, (pitch * 0.82 - innerGap * (count - 1)) / count);
    const group = count * bar + (count - 1) * innerGap;
    const start = month + (slot * (bar + innerGap) - group / 2) / pitch;
    return [start, start + bar / pitch];
}

/**
 * Create plot marks with explicit area baselines and unstacked grouped bars.
 *
 * @param {object} model Normalized chart model.
 * @param {Array<object>} series Visible series.
 * @param {Map<number,string>} fills Stable year fill patterns.
 * @param {number} pitch Width or height of a month in pixels.
 * @returns {Array<object>} Observable Plot marks.
 */
function createMarks(model, series, fills, pitch) {
    const marks = [];
    const horizontal = model.displayType === 'bar';
    const bars = horizontal || model.displayType === 'column';
    for (const [slot, item] of series.entries()) {
        const style = getSeriesStyle(model, item.year);
        const records = seriesRecords(item);
        if (bars) {
            const rectangles = records.filter((record) => record.value !== null).map((record) => {
                const [start, end] = groupBounds(record.month, slot, series.length, pitch);
                return {...record, start, end};
            });
            const axes = horizontal ? {x1: 0, x2: 'value', y1: 'start', y2: 'end'}
                : {x1: 'start', x2: 'end', y1: 0, y2: 'value'};
            marks.push(Plot.rect(rectangles, {...axes, fill: fills.get(item.year), rx: 1.5}));
            const zeros = rectangles.filter((record) => record.value === 0);
            if (zeros.length) {
                const zeroMark = horizontal ? Plot.ruleX(zeros, {x: 0, y1: 'start', y2: 'end', stroke: style.color, strokeWidth: 2})
                    : Plot.ruleY(zeros, {y: 0, x1: 'start', x2: 'end', stroke: style.color, strokeWidth: 2});
                marks.push(zeroMark);
            }
        } else {
            if (model.displayType === 'areaspline') {
                marks.push(Plot.areaY(records, {
                    x: 'month', y1: 0, y2: 'value', z: 'segment',
                    curve: 'monotone-x', fill: style.color, fillOpacity: 0.12,
                }));
            }
            marks.push(Plot.lineY(records, {
                x: 'month', y: 'value', z: 'segment', curve: 'monotone-x',
                stroke: style.color, strokeWidth: 2.5, strokeDasharray: style.dash || undefined,
            }));
            marks.push(Plot.dot(records.filter((record) => record.value !== null), {
                x: 'month', y: 'value', fill: '#fff', stroke: style.color,
                symbol: style.symbol, r: 3.4, strokeWidth: 1.8, strokeLinejoin: 'round',
            }));
        }
    }
    return marks;
}

/**
 * Add pointer targets and a selected-month marker without adding extra tab stops.
 *
 * @param {SVGElement} plot Plot SVG with scale accessors.
 * @param {object} model Normalized chart model.
 * @param {Array<object>} series Visible series.
 * @param {object} options Rendering options.
 * @param {object} layout Plot margins and size.
 * @returns {void}
 */
function appendInteraction(plot, model, series, options, layout) {
    const x = plot.scale('x');
    const y = plot.scale('y');
    const horizontal = model.displayType === 'bar';
    const bars = horizontal || model.displayType === 'column';
    const pitch = Math.abs((horizontal ? y : x).apply(2) - (horizontal ? y : x).apply(1));
    const overlay = svgElement('g', {'aria-hidden': 'true', class: 'rrze-statistik-point-targets'});
    for (const [slot, item] of series.entries()) {
        for (const record of seriesRecords(item)) {
            if (record.value === null) continue;
            let shape;
            if (bars) {
                const [start, end] = groupBounds(record.month, slot, series.length, pitch);
                const x1 = horizontal ? x.apply(0) : x.apply(start);
                const x2 = horizontal ? x.apply(record.value) : x.apply(end);
                const y1 = horizontal ? y.apply(start) : y.apply(0);
                const y2 = horizontal ? y.apply(end) : y.apply(record.value);
                const bounds = {
                    x: Math.min(x1, x2), y: Math.min(y1, y2),
                    width: Math.abs(x2 - x1), height: Math.abs(y2 - y1),
                };
                if (item.year === options.selectedYear && record.month === options.selectedMonth) {
                    const marker = svgElement('g', {class: 'rrze-statistik-selection', 'pointer-events': 'none'});
                    const outline = {...bounds, width: Math.max(1, bounds.width), height: Math.max(1, bounds.height),
                        rx: 1.5, fill: 'none'};
                    marker.append(svgElement('rect', {...outline, stroke: '#fff', 'stroke-width': 4}),
                        svgElement('rect', {...outline, stroke: '#17283d', 'stroke-width': 2}));
                    plot.append(marker);
                }
                if (record.value === 0) {
                    if (horizontal) bounds.width = 6;
                    else { bounds.y -= 6; bounds.height = 6; }
                }
                shape = svgElement('rect', bounds);
            } else {
                shape = svgElement('circle', {cx: x.apply(record.month), cy: y.apply(record.value), r: 10});
            }
            shape.setAttribute('fill', 'transparent');
            shape.setAttribute('pointer-events', 'all');
            shape.setAttribute('data-year', String(item.year));
            shape.setAttribute('data-month', String(record.month));
            overlay.append(shape);
        }
    }
    const selected = series.find((item) => item.year === options.selectedYear);
    const month = options.selectedMonth;
    if (!bars && selected && month >= 1 && month <= 12) {
        const value = selected.values[month - 1];
        const monthPosition = x.apply(month);
        const marker = svgElement('g', {class: 'rrze-statistik-selection', 'aria-hidden': 'true', 'pointer-events': 'none'});
        marker.append(svgElement('line', {
            x1: monthPosition, x2: monthPosition,
            y1: layout.marginTop, y2: layout.height - layout.marginBottom,
            stroke: '#1d2327', 'stroke-width': 1, 'stroke-dasharray': '3,3',
        }));
        if (value !== null) {
            marker.append(svgElement('circle', {
                cx: monthPosition,
                cy: y.apply(value),
                r: 6, fill: '#fff', stroke: '#111', 'stroke-width': 2.5,
            }));
        }
        plot.append(marker);
    }
    plot.append(overlay);
}

/**
 * Render a chart as self-contained SVG for both dashboard and local exports.
 *
 * @param {object} model Normalized chart model.
 * @param {number[]} visibleYears Years included in this view.
 * @param {{width:number,selectedYear?:number,selectedMonth?:number,exportMode?:boolean}} options Rendering options.
 * @returns {SVGSVGElement} Portable SVG with explicit dimensions.
 */
export function renderChartSvg(model, visibleYears, options = {}) {
    const width = Math.max(160, Math.round(Number(options.width) || 640));
    const horizontal = model.displayType === 'bar';
    const series = model.series.filter((item) => visibleYears.includes(item.year));
    const root = svgElement('svg', {
        width, role: 'img',
        style: 'background:#fff;color:#1d2327;font-family:Arial,sans-serif;font-size:12px;',
        class: 'rrze-statistik-svg',
    });
    const prefix = `rrze-chart-${++renderSequence}`;
    const heatmap = model.displayType === 'heatmap';
    const fills = heatmap ? null : appendPatterns(root, model, prefix);
    root.append(svgElement('title', {}, model.title));
    const period = formatVisibleYears(model, visibleYears);
    root.append(svgElement('desc', {}, `${model.description} ${period}. ${model.unit}.`));
    let headerHeight = 0;
    if (options.exportMode) {
        let baseline = appendWrappedText(root, model.title, width, 28, 20, 'bold');
        baseline = appendWrappedText(root, `${period} · ${model.unit}`, width, baseline, 13);
        let legendX = 20;
        for (const item of heatmap ? [] : series) {
            const style = getSeriesStyle(model, item.year);
            if (legendX + 130 > width) {
                legendX = 20;
                baseline += 26;
            }
            if (horizontal || model.displayType === 'column') {
                root.append(svgElement('rect', {x: legendX, y: baseline - 11, width: 32, height: 12, fill: fills.get(item.year)}));
            } else {
                root.append(svgElement('line', {
                    x1: legendX, x2: legendX + 32, y1: baseline - 5, y2: baseline - 5,
                    stroke: style.color, 'stroke-width': 3, 'stroke-dasharray': style.dash,
                }));
            }
            root.append(svgElement('text', {x: legendX + 42, y: baseline, fill: '#1d2327'}, String(item.year)));
            legendX += 130;
        }
        headerHeight = baseline + 10;
    }
    const plotHeight = heatmap ? drawHeatmap(root, model, series, options, width, headerHeight, prefix)
        : drawSeriesChart(root, model, series, options, width, headerHeight, fills);
    let height = headerHeight + plotHeight;
    if (options.exportMode) {
        height = appendWrappedText(root, `${model.sourceText}${model.sourceUrl ? ` — ${model.sourceUrl}` : ''}`, width, height + 22, 12) + 8;
    }
    root.setAttribute('height', String(height));
    root.setAttribute('viewBox', `0 0 ${width} ${height}`);
    return root;
}

/**
 * Draw monthly time series with axes shared by the interactive and exported views.
 * @param {SVGElement} root Outer SVG.
 * @param {object} model Normalized chart.
 * @param {object[]} series Visible years.
 * @param {object} options Active selection and export mode.
 * @param {number} width Drawing width in pixels.
 * @param {number} headerHeight Export metadata height.
 * @param {Map} fills Year textures.
 * @returns {number} Drawing height.
 */
function drawSeriesChart(root, model, series, options, width, headerHeight, fills) {
    const horizontal = model.displayType === 'bar';
    const values = series.flatMap((item) => item.values.filter((value) => value !== null));
    const maximum = Math.max(0, ...values) || 1;
    const compact = width < 650 && maximum >= 10000;
    const axisFormat = new Intl.NumberFormat(model.locale, compact
        ? {notation: 'compact', maximumFractionDigits: 1} : {maximumSignificantDigits: 12});
    const marginLeft = horizontal ? 40 : Math.max(40, axisFormat.format(maximum).length * 7 + 12);
    const layout = {width, height: horizontal ? 66 + 12 * (16 + 9 * series.length) : 326,
        marginTop: 28, marginRight: horizontal ? 34 : 12, marginBottom: 38, marginLeft};
    const span = horizontal ? layout.height - layout.marginTop - layout.marginBottom
        : layout.width - layout.marginLeft - layout.marginRight;
    const pitch = span / 12;
    const ticks = horizontal ? model.months.map((_, i) => i + 1) : monthTicks(model.months, span, options.selectedMonth);
    const monthScale = {
        domain: [0.5, 12.5], ticks, tickFormat: (month) => model.months[month - 1] || '',
        label: null, nice: false, tickSize: 0, tickPadding: 8,
    };
    const labelWidth = axisFormat.format(maximum).length * 7 + 16;
    const tickCount = horizontal ? Math.max(1, Math.floor((layout.width - layout.marginLeft - layout.marginRight) / labelWidth)) : 5;
    const valueScale = {
        domain: [0, maximum], nice: true, label: model.unit, labelArrow: false, tickSize: 0,
        ticks: Math.min(5, tickCount, Math.max(1, Math.ceil(maximum))), tickFormat: axisFormat.format,
    };
    const plot = Plot.plot({
        document,
        ...layout,
        style: {fontFamily: 'Arial, sans-serif', fontSize: '12px', color: '#1d2327', background: '#fff', overflow: 'visible'},
        x: horizontal ? valueScale : monthScale,
        y: horizontal ? {...monthScale, reverse: true} : valueScale,
        marks: [horizontal ? Plot.gridX({ticks: valueScale.ticks, stroke: '#dce3eb', strokeOpacity: 1})
            : Plot.gridY({ticks: valueScale.ticks, stroke: '#dce3eb', strokeOpacity: 1}),
            ...createMarks(model, series, fills, pitch)],
    });
    plot.setAttribute('x', '0');
    plot.setAttribute('y', String(headerHeight));
    plot.setAttribute('aria-hidden', 'true');
    if (!options.exportMode) appendInteraction(plot, model, series, options, layout);
    root.append(plot);
    return layout.height;
}
