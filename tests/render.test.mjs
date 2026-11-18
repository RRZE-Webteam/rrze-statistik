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
