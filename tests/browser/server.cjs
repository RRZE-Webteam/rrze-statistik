const http = require('node:http');
const fs = require('node:fs');
const path = require('node:path');

const metrics = ['visits', 'hits', 'hosts', 'files', 'kbytes'];

/** Build deterministic aggregate data with measured zero and deliberate gaps.
 * @param {URLSearchParams} params Fixture options.
 * @returns {Object} WordPress-compatible transfer payload.
 */
function createPayload(params) {
    const linechartDataset = [];
    for (let year = 2024; year <= 2026; year++) {
        for (let month = 1; month <= (year === 2026 ? 6 : 12); month++) {
            if (year === 2025 && month === 4) continue;
            const value = year === 2026 && month === 1 ? 0 : (year - 2023) * 100 + month * 10;
            linechartDataset.push({year: String(year), month: String(month), visits: String(value),
                hits: String(value * 8), hosts: String(value / 2), files: String(value * 4), kbytes: String(value * 12.75)});
        }
    }
    return {
        currentYear: 2026, locale: params.get('locale') || 'en-GB',
        linechartDataset: params.get('scenario') === 'empty' ? [] : linechartDataset,
        displayType: params.get('type') || 'spline',
        languagePackage: Object.fromEntries(metrics.map((metric) => [metric, {headline_chart: `${metric} over time`, ordinate_desc: metric}])),
        descriptions: {visits: 'Monthly visits compared across three calendar years.'},
        sourceText: 'Source: statistics.example.test', sourceUrl: 'https://statistics.example.test/',
    };
}

/** Serve production assets and isolated dashboard fixtures without a live database.
 * @param {import('node:http').IncomingMessage} request HTTP request.
 * @param {import('node:http').ServerResponse} response HTTP response.
 * @returns {void}
 */
function serve(request, response) {
    const url = new URL(request.url, 'http://127.0.0.1:8765');
    if (url.pathname.startsWith('/dist/')) {
        const filename = path.basename(url.pathname);
        const file = path.join(__dirname, '../../dist', filename);
        if (!fs.existsSync(file)) { response.writeHead(404).end(); return; }
        response.setHeader('Content-Type', filename.endsWith('.css') ? 'text/css' : 'application/javascript');
        response.end(fs.readFileSync(file));
        return;
    }
    const payload = createPayload(url.searchParams);
    const shownMetrics = url.searchParams.has('all') ? metrics : ['visits'];
    response.setHeader('Content-Type', 'text/html; charset=utf-8');
    response.end(`<!doctype html><html lang="en"><head><meta charset="utf-8"><title>Statistics integration test</title>
        <meta name="viewport" content="width=device-width, initial-scale=1"><link rel="stylesheet" href="/dist/charts.css">
        <style>body{font:14px Arial,sans-serif;background:#f0f0f1;color:#1d2327;margin:0;padding:16px}main{display:grid;gap:20px;grid-template-columns:repeat(auto-fit,minmax(min(100%,320px),1fr))}.postbox{min-width:0;max-width:820px;padding:12px;background:white;border:1px solid #c3c4c7}h1{font-size:24px}</style>
        </head><body><h1>Statistics dashboard</h1><main>${shownMetrics.map((metric) => `<section class="postbox" aria-label="${metric}"><div class="rrze-statistik-chart" id="${metric}" data-statistik-chart data-metric="${metric}">Loading chart…</div></section>`).join('')}</main>
        <script>window.RRZESTATISTIKTRANSFER=${JSON.stringify(payload).replace(/</g, '\\u003c')};</script>
        <script src="/dist/chartsIndex.js"></script></body></html>`);
}

http.createServer(serve).listen(8765, '127.0.0.1');
