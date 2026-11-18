import assert from 'node:assert/strict';
import test from 'node:test';
import { JSDOM } from 'jsdom';
import { DEFAULT_LABELS } from '../src/charts/model.js';
import { createCsv, mountExportControls, serializeChartSvg } from '../src/charts/exports.js';

/**
 * Create a deliberately small normalized model with zeros and missing months.
 *
 * @returns {object} Export fixture.
 */
function fixture() {
    return {
        metric: 'visits',
        title: 'Besuche & Überblick',
        unit: 'Besuche',
        description: 'Monatliche Besuche im Vergleich.',
        sourceText: 'Quelle',
        sourceUrl: 'https://statistik.example.test/',
        locale: 'de-DE',
        displayType: 'spline',
        years: [2024, 2025, 2026],
        months: ['Jan', 'Feb', 'Mär', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez'],
        labels: { ...DEFAULT_LABELS, month: 'Monat' },
        series: [
            { year: 2024, values: [0, 1234, ...Array(10).fill(null)] },
            { year: 2025, values: Array(12).fill(1) },
            { year: 2026, values: [2, null, 12.5, ...Array(9).fill(null)] },
        ],
    };
}

/**
 * CSV preserves real zeros and raw numbers while leaving missing data blank.
 */
test('CSV exports only visible series with twelve months and CRLF endings', () => {
    const model = fixture();
    const csv = createCsv(model, [2026, 2024]);
    const rows = csv.split('\r\n');
    assert.equal(rows[0], 'Monat,2024,2026');
    assert.equal(rows[1], 'Jan,0,2');
    assert.equal(rows[2], 'Feb,1234,');
    assert.equal(rows[3], 'Mär,,12.5');
    assert.equal(rows[12], 'Dez,,');
    assert.equal(rows.length, 14);
    assert.equal(rows[13], '');
    assert.ok(!csv.startsWith('\uFEFF'));
    assert.ok(!csv.includes('2025'));
    assert.ok(!csv.includes('undefined'));
    assert.ok(!csv.includes('null'));
});

/**
 * Translated text must remain valid CSV even when it contains delimiters.
 */
test('CSV escapes commas, quotes and line breaks in translated fields', () => {
    const model = fixture();
    model.labels.month = 'Monat, "Name"';
    model.months[0] = 'Jan\n2024';
    const csv = createCsv(model, [2024]);
    assert.ok(csv.startsWith('"Monat, ""Name""",2024\r\n"Jan\n2024",0\r\n'));
});

/**
 * Unexpected non-finite values must never leak as textual measurements.
 */
test('CSV leaves invalid values blank and ignores duplicate/unknown visible years', () => {
    const model = fixture();
    model.series[0].values = [NaN, Infinity, undefined, ...Array(9).fill(null)];
    const rows = createCsv(model, [2024, 2024, 1999]).split('\r\n');
    assert.equal(rows[0], 'Monat,2024');
    assert.equal(rows[1], 'Jan,');
    assert.equal(rows[2], 'Feb,');
    assert.equal(rows[3], 'Mär,');
});

/**
 * Install a short-lived DOM with matching event/abort realms for toolbar tests.
 *
 * @param {import('node:test').TestContext} context Test lifecycle context.
 * @returns {JSDOM} Isolated DOM restored automatically after the test.
 */
function installDom(context) {
    const dom = new JSDOM('<!doctype html><html><body><div id="toolbar"></div></body></html>', {
        url: 'https://statistik.example.test/',
    });
    const globals = ['document', 'navigator', 'AbortController', 'XMLSerializer'];
    const originals = globals.map((name) => [name, Object.getOwnPropertyDescriptor(globalThis, name)]);
    for (const name of globals) {
        Object.defineProperty(globalThis, name, {
            configurable: true,
            value: dom.window[name],
        });
    }
    context.after(() => {
        for (const [name, descriptor] of originals) {
            if (descriptor) Object.defineProperty(globalThis, name, descriptor);
            else delete globalThis[name];
        }
        dom.window.close();
    });
    return dom;
}

/**
 * Allow asynchronous clipboard success/error handlers to finish.
 *
 * @returns {Promise<void>} Completion after queued promise callbacks.
 */
function flushAsyncActions() {
    return new Promise((resolve) => setImmediate(resolve));
}

/**
 * The actual renderer must serialize to an independent, complete SVG document.
 */
test('SVG export is standalone and includes metadata without interaction targets', (context) => {
    const dom = installDom(context);
    const model = fixture();
    const markup = serializeChartSvg(model, [2024, 2026]);
    const parsed = new dom.window.DOMParser().parseFromString(markup, 'image/svg+xml');
    assert.equal(parsed.querySelector('parsererror'), null);
    assert.equal(parsed.documentElement.namespaceURI, 'http://www.w3.org/2000/svg');
    assert.equal(parsed.documentElement.getAttribute('width'), '1200');
    assert.ok(Number(parsed.documentElement.getAttribute('height')) > 0);
    assert.ok(parsed.documentElement.textContent.includes(model.title));
    assert.ok(parsed.documentElement.textContent.includes(model.sourceUrl));
    assert.ok(parsed.documentElement.textContent.includes(model.unit));
    assert.equal(parsed.querySelector('foreignObject'), null);
    assert.equal(parsed.querySelector('.rrze-statistik-point-targets'), null);
    assert.equal(parsed.querySelector('script'), null);
});

/**
 * Clipboard rejection must leave the accessible error and download alternative.
 */
test('clipboard rejection keeps CSV download usable with BOM only in the file', async (context) => {
    const dom = installDom(context);
    const model = fixture();
    let copied;
    Object.defineProperty(dom.window.navigator, 'clipboard', {
        value: {
            /**
             * Simulate denied clipboard permission after receiving CSV text.
             * @param {string} value Clipboard content.
             * @returns {Promise<never>} Permission rejection.
             */
            async writeText(value) {
                copied = value;
                throw new Error('Permission denied');
            },
        },
    });
    const blobs = [];
    const revoked = [];
    const links = [];
    context.mock.method(URL, 'createObjectURL', (blob) => {
        blobs.push(blob);
        return `blob:export-${blobs.length}`;
    });
    context.mock.method(URL, 'revokeObjectURL', (url) => revoked.push(url));
    context.mock.method(dom.window.HTMLAnchorElement.prototype, 'click', function () {
        links.push({ href: this.href, download: this.download });
    });

    const toolbar = document.getElementById('toolbar');
    const dispose = mountExportControls(toolbar, model, () => [2024, 2026]);
    toolbar.querySelector('[data-export="copy"]').click();
    await flushAsyncActions();
    assert.equal(toolbar.querySelector('[role="status"]').textContent, model.labels.copyError);
    assert.equal(copied, createCsv(model, [2024, 2026]));
    assert.ok(!copied.startsWith('\uFEFF'));
    assert.equal(toolbar.querySelectorAll('button').length, 4);

    toolbar.querySelector('[data-export="csv"]').click();
    assert.equal(links[0].download, 'rrze-statistik-visits-2024-2026.csv');
    const bytes = new Uint8Array(await blobs[0].arrayBuffer());
    assert.deepEqual([...bytes.slice(0, 3)], [0xEF, 0xBB, 0xBF]);
    assert.equal(await blobs[0].text(), createCsv(model, [2024, 2026]));
    assert.equal(toolbar.querySelector('[role="status"]').textContent, model.labels.exported);

    dispose();
    assert.deepEqual(revoked, ['blob:export-1']);
    assert.equal(toolbar.children.length, 0);
});

/**
 * Successful clipboard writes use the latest visible-year selection.
 */
test('copy reads the current selection and disposal removes event listeners', async (context) => {
    const dom = installDom(context);
    const model = fixture();
    const copied = [];
    Object.defineProperty(dom.window.navigator, 'clipboard', {
        value: {
            /**
             * Capture an allowed clipboard write.
             * @param {string} value Clipboard content.
             * @returns {Promise<void>} Successful write.
             */
            async writeText(value) { copied.push(value); },
        },
    });
    let visibleYears = [2024, 2026];
    const toolbar = document.getElementById('toolbar');
    const dispose = mountExportControls(toolbar, model, () => visibleYears);
    const copy = toolbar.querySelector('[data-export="copy"]');
    visibleYears = [2025];
    copy.click();
    await flushAsyncActions();
    assert.deepEqual(copied, [createCsv(model, [2025])]);
    assert.equal(toolbar.querySelector('[role="status"]').textContent, model.labels.copied);
    assert.equal(copy.hasAttribute('aria-disabled'), false);
    dispose();
    copy.click();
    await flushAsyncActions();
    assert.equal(copied.length, 1);
});

/**
 * Download URLs are reclaimed both after a completed click and after failure.
 */
test('download URLs are revoked after consumption and after download errors', (context) => {
    const dom = installDom(context);
    context.mock.timers.enable({ apis: ['setTimeout'] });
    const model = fixture();
    const revoked = [];
    let counter = 0;
    context.mock.method(URL, 'createObjectURL', () => `blob:export-${++counter}`);
    context.mock.method(URL, 'revokeObjectURL', (url) => revoked.push(url));
    const anchorClick = context.mock.method(dom.window.HTMLAnchorElement.prototype, 'click', () => {});
    const toolbar = document.getElementById('toolbar');
    const dispose = mountExportControls(toolbar, model, () => [2024]);
    const csv = toolbar.querySelector('[data-export="csv"]');

    csv.click();
    assert.deepEqual(revoked, []);
    context.mock.timers.tick(1000);
    assert.deepEqual(revoked, ['blob:export-1']);

    anchorClick.mock.mockImplementation(() => { throw new Error('Download failed'); });
    csv.click();
    assert.deepEqual(revoked, ['blob:export-1', 'blob:export-2']);
    assert.equal(toolbar.querySelector('[role="status"]').textContent, model.labels.exportError);
    assert.equal(document.querySelector('a[download]'), null);
    dispose();
    assert.deepEqual(revoked, ['blob:export-1', 'blob:export-2']);
});
