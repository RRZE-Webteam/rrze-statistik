import test from 'node:test';
import assert from 'node:assert/strict';
import {JSDOM} from 'jsdom';
import {createChartModel} from '../src/charts/model.js';
import {getSeriesStyle, renderChartSvg} from '../src/charts/render.js';

globalThis.document = new JSDOM('<!doctype html><html><body></body></html>').window.document;

/**
 * Build a deterministic fixture containing a real zero and an explicit month gap.
 * @param {string} displayType Requested chart layout.
 * @returns {object} Normalized fixture model.
 */
function fixture(displayType = 'spline') {
    return createChartModel({
        currentYear: 2026, locale: 'de-DE', displayType,
        sourceText: 'Quelle: Statistik', sourceUrl: 'https://example.test/statistik',
        descriptions: {visits: 'Monatliche Besuche'},
        languagePackage: {visits: {headline_chart: 'Besuche über die Jahre', ordinate_desc: 'Besuche'}},
        linechartDataset: [
            {year: 2024, month: 1, visits: 2},
            {year: 2025, month: 1, visits: 4},
            {year: 2026, month: 1, visits: 6},
            {year: 2026, month: 2, visits: 0},
            {year: 2026, month: 4, visits: 12},
        ],
    }, 'visits');
}

test('all supported charts produce standalone finite SVG geometry', () => {
    for (const type of ['spline', 'areaspline', 'column', 'bar']) {
        const model = fixture(type);
        const svg = renderChartSvg(model, model.years, {width: 1200, exportMode: true});
        assert.equal(svg.namespaceURI, 'http://www.w3.org/2000/svg');
        assert.equal(svg.getAttribute('width'), '1200');
        assert.ok(Number(svg.getAttribute('height')) > 350);
        assert.equal(svg.querySelector('foreignObject'), null);
        assert.doesNotMatch(svg.outerHTML, /NaN|Infinity/);
        assert.match(svg.textContent, /Besuche über die Jahre/);
        assert.match(svg.textContent, /2024–2026/);
        assert.match(svg.textContent, /https:\/\/example.test\/statistik/);
        assert.equal(svg.querySelector('.rrze-statistik-selection'), null);
        assert.equal(svg.querySelector('[data-month]'), null);
    }
});

test('line and area marks split around missing months instead of bridging them', () => {
    for (const type of ['spline', 'areaspline']) {
        const model = fixture(type);
        const svg = renderChartSvg(model, [2026], {width: 640});
        assert.equal(svg.querySelector('g[aria-label="line"]').querySelectorAll('path').length, 2);
        if (type === 'areaspline') {
            assert.equal(svg.querySelector('g[aria-label="area"]').querySelectorAll('path').length, 2);
        }
        assert.equal(svg.querySelector('g[aria-label="dot"]').children.length, 3);
    }
});

test('columns share a zero baseline and occupy separate slots within each month', () => {
    const model = fixture('column');
    const svg = renderChartSvg(model, model.years, {width: 640});
    const rectGroups = [...svg.querySelectorAll('g[aria-label="rect"]')];
    const january = rectGroups.map((group) => group.querySelector('rect'));
    const bounds = january.map((rect) => ({
        left: Number(rect.getAttribute('x')), width: Number(rect.getAttribute('width')),
        bottom: Number(rect.getAttribute('y')) + Number(rect.getAttribute('height')),
    }));
    assert.equal(bounds.length, 3);
    assert.ok(bounds[0].left + bounds[0].width < bounds[1].left);
    assert.ok(bounds[1].left + bounds[1].width < bounds[2].left);
    assert.ok(bounds.every((bound) => Math.abs(bound.bottom - bounds[0].bottom) < 0.01));
});

test('horizontal bars share a zero baseline and separate the three year slots', () => {
    const model = fixture('bar');
    const svg = renderChartSvg(model, model.years, {width: 640});
    const january = [...svg.querySelectorAll('g[aria-label="rect"]')].map((group) => group.querySelector('rect'));
    assert.equal(new Set(january.map((rect) => rect.getAttribute('x'))).size, 1);
    const positions = january.map((rect) => Number(rect.getAttribute('y')));
    assert.equal(new Set(positions).size, 3);
});

test('zero values remain selectable while missing values get no invented point', () => {
    const model = fixture();
    const zero = renderChartSvg(model, [2026], {width: 320, selectedYear: 2026, selectedMonth: 2});
    assert.ok(zero.querySelector('[data-year="2026"][data-month="2"]'));
    assert.ok(zero.querySelector('.rrze-statistik-selection circle'));
    const missing = renderChartSvg(model, [2026], {width: 320, selectedYear: 2026, selectedMonth: 3});
    assert.equal(missing.querySelector('[data-year="2026"][data-month="3"]'), null);
    assert.equal(missing.querySelector('.rrze-statistik-selection circle'), null);
    assert.ok(missing.querySelector('.rrze-statistik-selection line'));
});

test('stable identities and local pattern IDs survive filtering and simultaneous charts', () => {
    const model = fixture('column');
    const style = getSeriesStyle(model, 2026);
    assert.equal(style.color, '#6b3fa0');
    const first = renderChartSvg(model, [2026], {width: 640});
    const second = renderChartSvg(model, model.years, {width: 640});
    const firstIds = [...first.querySelectorAll('[id]')].map((node) => node.id);
    const secondIds = [...second.querySelectorAll('[id]')].map((node) => node.id);
    assert.ok(firstIds.every((id) => !secondIds.includes(id)));
    for (const svg of [first, second]) {
        for (const match of svg.outerHTML.matchAll(/url\(#([^\)]+)\)/g)) {
            assert.ok(svg.querySelector(`[id="${match[1]}"]`), `Unresolved pattern: ${match[1]}`);
        }
    }
});

test('empty data and very long export metadata are represented without invalid geometry', () => {
    const model = createChartModel({currentYear: 2026, linechartDataset: []}, 'visits');
    model.title = 'Sehr langer Diagrammtitel '.repeat(20);
    model.sourceUrl = `https://example.test/${'statistics'.repeat(50)}`;
    const svg = renderChartSvg(model, model.years, {width: 1200, exportMode: true});
    assert.doesNotMatch(svg.outerHTML, /NaN|Infinity/);
    assert.ok(Number(svg.getAttribute('height')) > 500);
    assert.ok(svg.querySelectorAll(':scope > text').length > 10);
});

/** Verify measured zero has geometry and an empty month occupies only its group slot. */
test('grouped bars distinguish a measured zero, a selected measurement and a gap', () => {
    for (const type of ['bar', 'column']) {
        const model = fixture(type);
        const svg = renderChartSvg(model, model.years, {width: 468, selectedYear: 2026, selectedMonth: 2});
        const zero = svg.querySelector('[data-year="2026"][data-month="2"]');
        assert.ok(zero);
        assert.ok(svg.querySelector(type === 'bar' ? 'g[aria-label="rule"] line, g[aria-label="ruleX"] line'
            : 'g[aria-label="rule"] line, g[aria-label="ruleY"] line'));
        assert.ok(svg.querySelector('.rrze-statistik-selection rect'));
        assert.equal(svg.querySelector('.rrze-statistik-selection circle'), null);
        assert.equal(svg.querySelector('[data-year="2026"][data-month="3"]'), null);
        if (type === 'bar') assert.ok(+renderChartSvg(model, [2026], {width: 468}).getAttribute('height') < +svg.getAttribute('height'));
    }
});

/** Compare actual SVG fills across filtered and exported matrices, including bin edges. */
test('heatmap filters keep thresholds stable and distinguish zero, missing and future', () => {
    const model = fixture('heatmap');
    model.currentMonth = 6;
    model.series[0].values[0] = 120;
    model.series[2].values[0] = 30;
    const all = renderChartSvg(model, model.years, {width: 468});
    const single = renderChartSvg(model, [2026], {width: 1200, exportMode: true});
    const allCells = all.querySelectorAll('g[aria-label="cell"] rect');
    const cells = single.querySelectorAll('g[aria-label="cell"] rect');
    assert.equal(allCells.length, 36);
    assert.equal(cells.length, 12);
    assert.equal(cells[0].getAttribute('fill'), allCells[24].getAttribute('fill'));
    const key = [...single.querySelectorAll('.rrze-statistik-heat-key rect')];
    assert.equal(cells[0].getAttribute('fill'), key[1].getAttribute('fill'));
    assert.equal(cells[1].getAttribute('fill'), key[0].getAttribute('fill'));
    assert.match(cells[2].getAttribute('fill'), /url\(#.+missing\)/);
    assert.equal(cells[6].getAttribute('fill'), '#fff');
    assert.equal(single.querySelector('[data-month]'), null);
    assert.match(single.textContent, /≤ 120/);
    assert.match(single.textContent, /2026 · Besuche/);
    assert.match(single.textContent, /https:\/\/example.test/);
    model.series[2].values.fill(null);
    model.series[2].values[0] = 0;
    const zeroKey = renderChartSvg(model, [2026]).querySelector('.rrze-statistik-heat-key').textContent;
    assert.match(zeroKey, /0/);
    assert.doesNotMatch(zeroKey, /≤/);
    model.series[2].values[0] = null;
    assert.doesNotMatch(renderChartSvg(model, [2026]).querySelector('.rrze-statistik-heat-key').textContent, /≤|0/);
});
