const {test, expect} = require('@playwright/test');
const AxeBuilder = require('@axe-core/playwright').default;
const fs = require('node:fs/promises');

/** Open the production bundle inside an isolated dashboard fixture.
 * @param {import('@playwright/test').Page} page Browser page.
 * @param {string} query Fixture options.
 * @returns {Promise<import('@playwright/test').Locator>} Mounted chart root.
 */
async function openChart(page, query = '') {
    await page.goto(`/${query}`);
    const root = page.locator('#visits');
    await expect(root.locator('.rrze-statistik-plot > svg')).toBeVisible();
    return root;
}

/** Read an actual downloaded artifact rather than mocking the export path.
 * @param {import('@playwright/test').Page} page Browser page.
 * @param {import('@playwright/test').Locator} root Chart root.
 * @param {string} kind Export kind.
 * @returns {Promise<Buffer>} File content.
 */
async function downloadBytes(page, root, kind) {
    const pending = page.waitForEvent('download');
    await root.locator(`[data-export="${kind}"]`).click();
    const download = await pending;
    expect(download.suggestedFilename()).toMatch(new RegExp(`^rrze-statistik-visits-\\d{4}-\\d{4}\\.${kind}$`));
    return fs.readFile(await download.path());
}

for (const type of ['spline', 'areaspline', 'column', 'bar']) {
    test(`${type}: keyboard exploration, visibility, table and accessibility`, async ({page}) => {
        const root = await openChart(page, `?type=${type}`);
        const slider = root.locator('input[type="range"]');
        await expect(slider).toHaveValue('6');
        await slider.focus();
        await page.keyboard.press('Home');
        await expect(slider).toHaveValue('1');
        await expect(slider).toHaveAttribute('aria-valuetext', /January 2026: 0 visits/);
        await page.keyboard.press('End');
        await expect(slider).toHaveValue('12');
        await expect(slider).toHaveAttribute('aria-valuetext', /No data available/);
        await root.locator('select').selectOption('2025');
        await slider.focus();
        await page.keyboard.press('Home');
        await page.keyboard.press('ArrowRight');
        await expect(slider).toHaveValue('2');
        await expect(root.locator('output')).toContainText('February 2025');
        await root.locator('summary').click();
        await expect(root.locator('tbody tr')).toHaveCount(12);
        await expect(root.locator('thead th')).toHaveCount(4);
        await root.locator('[data-year="2024"].rrze-statistik-year-toggle').click();
        await expect(root.locator('thead th')).toHaveCount(3);
        const csv = await downloadBytes(page, root, 'csv');
        expect(csv.toString('utf8')).toMatch(/^\uFEFFMonth,2025,2026\r\n/);
        expect(csv.toString('utf8')).toContain('Jan,210,0');
        const accessibility = await new AxeBuilder({page}).include('#visits').withTags(['wcag2a', 'wcag2aa', 'wcag21aa']).analyze();
        expect(accessibility.violations).toEqual([]);
    });
}

test('SVG and PNG downloads are complete, standalone and local', async ({page}) => {
    const requests = [];
    page.on('request', (request) => requests.push(request.url()));
    const root = await openChart(page, '?type=column');
    const svg = (await downloadBytes(page, root, 'svg')).toString('utf8');
    expect(svg).toContain('visits over time');
    expect(svg).toContain('https://statistics.example.test/');
    expect(svg).toContain('2024');
    expect(svg).not.toContain('foreignObject');
    expect(await page.evaluate((markup) => new DOMParser().parseFromString(markup, 'image/svg+xml').querySelectorAll('parsererror').length, svg)).toBe(0);
    const png = await downloadBytes(page, root, 'png');
    expect(png.subarray(1, 4).toString()).toBe('PNG');
    expect(png.readUInt32BE(16)).toBe(2400);
    expect(png.readUInt32BE(20)).toBeGreaterThan(500);
    expect(requests.filter((url) => !url.startsWith('http://127.0.0.1:8765/') && !url.startsWith('blob:'))).toEqual([]);
});

test('clipboard denial preserves CSV download', async ({page}) => {
    const root = await openChart(page);
    await page.evaluate(() => Object.defineProperty(navigator, 'clipboard', {configurable: true, value: {
        /** Simulate a browser privacy refusal. @returns {Promise<void>} Rejected copy. */
        async writeText() { throw new DOMException('Denied', 'NotAllowedError'); },
    }}));
    await root.locator('[data-export="copy"]').click();
    await expect(root.locator('.rrze-statistik-export-status')).toHaveText(/Copying failed/);
    expect((await downloadBytes(page, root, 'csv')).length).toBeGreaterThan(100);
});

test('all metrics survive narrow layouts, focus-preserving resize and remount', async ({page}) => {
    const errors = [];
    page.on('pageerror', (error) => errors.push(error.message));
    const root = await openChart(page, '?all=1');
    await expect(page.locator('[data-statistik-chart] .rrze-statistik-plot > svg')).toHaveCount(5);
    const slider = root.locator('input[type="range"]');
    await slider.focus();
    await page.keyboard.press('Home');
    await page.keyboard.press('ArrowRight');
    await page.setViewportSize({width: 320, height: 900});
    await expect(slider).toBeFocused();
    await expect(slider).toHaveValue('2');
    await expect.poll(() => page.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(320);
    await page.evaluate(() => {
        const root = document.getElementById('visits');
        const fresh = root.cloneNode(false);
        root.replaceWith(fresh);
    });
    await expect(page.locator('#visits [data-export="csv"]')).toHaveCount(1);
    await expect(page.locator('#visits .rrze-statistik-plot > svg')).toHaveCount(1);
    expect(errors).toEqual([]);
});

test('empty datasets retain table, CSV and an honest missing-data message', async ({page}) => {
    const root = await openChart(page, '?scenario=empty');
    await expect(root.locator('.rrze-statistik-summary')).toHaveText(/No data/);
    await expect(root.locator('input[type="range"]')).toHaveAttribute('aria-valuetext', /No data/);
    await root.locator('summary').click();
    await expect(root.locator('tbody tr')).toHaveCount(12);
    const csv = (await downloadBytes(page, root, 'csv')).toString('utf8');
    expect(csv).toContain('Jan,,,\r\n');
});

test('renderer failure preserves the data table and CSV', async ({page}) => {
    await page.addInitScript(() => {
        const create = document.createElementNS.bind(document);
        /** Simulate an unavailable SVG renderer without disabling HTML controls.
         * @param {string} namespace Namespace URI.
         * @param {string} tag Element tag.
         * @param {Object} options Creation options.
         * @returns {Element} Non-SVG element.
         */
        document.createElementNS = function failSvg(namespace, tag, options) {
            if (namespace === 'http://www.w3.org/2000/svg') throw new Error('Simulated renderer failure');
            return create(namespace, tag, options);
        };
    });
    await page.goto('/');
    const root = page.locator('#visits');
    await expect(root.locator('.rrze-statistik-render-error')).toHaveText(/could not be displayed/);
    await root.locator('summary').click();
    await expect(root.locator('tbody tr')).toHaveCount(12);
    expect((await downloadBytes(page, root, 'csv')).length).toBeGreaterThan(100);
});

test('visual preview and enlarged layout', async ({page}, testInfo) => {
    const root = await openChart(page, '?type=areaspline');
    await root.screenshot({path: testInfo.outputPath('chart-preview.png')});
    await page.addStyleTag({content: 'html { zoom: 2; }'});
    await expect(root.locator('input[type="range"]')).toBeVisible();
    await expect.poll(() => page.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(1280);
    await root.locator('input[type="range"]').press('Home');
    await expect(root.locator('output')).toContainText('January 2026: 0 visits');
});
