const {test, expect} = require('@playwright/test');
const AxeBuilder = require('@axe-core/playwright').default;
const fs = require('node:fs/promises');

/**
 * Open the production bundle in a single-widget dashboard fixture.
 * @param {import('@playwright/test').Page} page Browser page.
 * @returns {Promise<import('@playwright/test').Locator>} Enhanced dashboard.
 */
async function openDashboard(page) {
    await page.goto('/?dashboard=1');
    const root = page.locator('[data-statistik-dashboard]');
    await expect(root.getByRole('tab', {name: 'visits', exact: true})).toHaveAttribute('aria-selected', 'true');
    return root;
}

test('tabs support keyboard navigation, lazy charts and retained exploration state', async ({page}) => {
    const root = await openDashboard(page);
    const visits = root.locator('#visits');
    const tabs = root.getByRole('tab');
    await expect(tabs).toHaveCount(6);
    await expect(root.getByRole('tabpanel')).toHaveCount(1);
    await expect(root.locator('.rrze-statistik-plot > svg')).toHaveCount(1);
    await visits.locator('select').selectOption('2025');
    await visits.locator('input[type="range"]').press('Home');
    await visits.locator('[data-year="2024"].rrze-statistik-year-toggle').click();
    await visits.locator('summary').click();
    const visitsTab = root.getByRole('tab', {name: 'visits', exact: true});
    await visitsTab.focus();
    await page.keyboard.press('ArrowRight');
    await expect(root.getByRole('tab', {name: 'hits', exact: true})).toBeFocused();
    await expect(root.locator('#hits .rrze-statistik-plot > svg')).toBeVisible();
    await expect(root.locator('#visits')).toBeHidden();
    await page.keyboard.press('Home');
    await expect(visits.locator('select')).toHaveValue('2025');
    await expect(visits.locator('input[type="range"]')).toHaveValue('1');
    await expect(visits.locator('details')).toHaveAttribute('open', '');
    await expect(visits.locator('thead th')).toHaveCount(3);
    await page.keyboard.press('End');
    await expect(root.getByRole('tab', {name: 'Popular pages'})).toBeFocused();
    await expect(root.getByRole('table', {name: 'Sites', exact: true})).toBeVisible();
    await expect(root.getByLabel('Display type')).toBeHidden();
    await expect(root.locator('[data-export]:visible')).toHaveCount(0);
    await page.keyboard.press('ArrowRight');
    await expect(visitsTab).toBeFocused();
    const accessibility = await new AxeBuilder({page}).include('[data-statistik-dashboard]').withTags(['wcag2a', 'wcag2aa', 'wcag21aa']).analyze();
    expect(accessibility.violations).toEqual([]);
});

test('inline chart type preserves selection and drives current-metric exports and reload', async ({page}) => {
    const root = await openDashboard(page);
    await root.getByRole('tab', {name: 'hits', exact: true}).click();
    const chart = root.locator('#hits');
    await chart.locator('select').selectOption('2025');
    await chart.locator('input[type="range"]').press('Home');
    await chart.locator('[data-year="2024"].rrze-statistik-year-toggle').click();
    const typeSelect = root.getByLabel('Display type');
    await typeSelect.focus();
    for (const type of ['areaspline', 'column', 'spline', 'bar', 'heatmap']) {
        await typeSelect.selectOption(type);
        await expect(typeSelect).toBeFocused();
        await expect(chart.locator('.rrze-statistik-description')).toHaveText(`${type} chart: monthly hits.`);
        await expect(chart.locator('select')).toHaveValue('2025');
        await expect(chart.locator('input[type="range"]')).toHaveValue('1');
        await expect(chart.locator('.rrze-statistik-year-toggle[aria-pressed="true"]')).toHaveCount(2);
        if (type === 'heatmap') {
            await expect(chart.locator('.rrze-statistik-point-targets rect')).toHaveCount(24);
            continue;
        }
        const mark = type === 'spline' ? 'line' : type === 'areaspline' ? 'area' : 'rect';
        await expect(chart.locator(`g[aria-label="${mark}"]`)).toHaveCount(2);
    }
    const downloadReady = page.waitForEvent('download');
    await chart.locator('[data-export="svg"]').click();
    const download = await downloadReady;
    expect(download.suggestedFilename()).toBe('rrze-statistik-hits-2025-2026.svg');
    const svg = await fs.readFile(await download.path(), 'utf8');
    expect(svg).toContain('hits over time');
    expect(svg).toContain('Upper limits');
    expect(svg).not.toContain('aria-label="line"');
    await expect.poll(async () => (await page.context().cookies()).find((cookie) => cookie.name === 'rrze-test-displayType')?.value).toBe('heatmap');
    await page.reload();
    await expect(root.getByRole('tab', {name: 'hits', exact: true})).toHaveAttribute('aria-selected', 'true');
    await expect(root.getByLabel('Display type')).toHaveValue('heatmap');
    await expect(root.locator('#hits .rrze-statistik-point-targets rect')).toHaveCount(36);
});

test('preference failures remain usable and can be retried', async ({page}) => {
    let rejectNext = true;
    await page.route('**/preferences', async (route) => {
        if (rejectNext) {
            rejectNext = false;
            await route.fulfill({status: 500, json: {success: false}});
        } else {
            await route.continue();
        }
    });
    const root = await openDashboard(page);
    await root.getByRole('tab', {name: 'files', exact: true}).click();
    await expect(root.locator('#files .rrze-statistik-plot > svg')).toBeVisible();
    await expect(root.locator('.rrze-statistik-preferences-status')).toHaveText(/could not be saved/);
    await root.getByRole('button', {name: 'Retry saving'}).click();
    await expect(root.locator('.rrze-statistik-preferences-feedback')).toBeHidden();
    await expect.poll(async () => (await page.context().cookies()).find((cookie) => cookie.name === 'rrze-test-view')?.value).toBe('files');
    await page.reload();
    await expect(root.getByRole('tab', {name: 'files', exact: true})).toHaveAttribute('aria-selected', 'true');
});

test('rapid switches save in order and finish with the latest selection', async ({page}, testInfo) => {
    const requests = [];
    let releaseFirst;
    const firstGate = new Promise((resolve) => { releaseFirst = resolve; });
    await page.route('**/preferences', async (route) => {
        requests.push(Object.fromEntries(new URLSearchParams(route.request().postData())));
        if (requests.length === 1) await firstGate;
        await route.continue();
    });
    const root = await openDashboard(page);
    await root.getByRole('tab', {name: 'hits', exact: true}).click();
    await expect.poll(() => requests.length).toBe(1);
    await root.getByRole('tab', {name: 'hosts', exact: true}).click();
    await root.getByLabel('Display type').selectOption('bar');
    await root.getByRole('tab', {name: 'kbytes', exact: true}).click();
    expect(requests).toHaveLength(1);
    await expect(root.locator('.rrze-statistik-preferences-progress')).toHaveText('Saving selection…');
    await expect(root.locator('.rrze-statistik-save-indicator')).toBeVisible();
    await root.locator('.rrze-statistik-dashboard-controls').screenshot({path: testInfo.outputPath('saving-preview.png')});
    releaseFirst();
    await expect.poll(async () => (await page.context().cookies()).find((cookie) => cookie.name === 'rrze-test-view')?.value).toBe('kbytes');
    expect(requests).toHaveLength(2);
    expect(requests[1]).toMatchObject({view: 'kbytes', displayType: 'bar'});
    await expect(root.locator('.rrze-statistik-preferences-progress')).toHaveText('Selection saved.');
});

test('unchanged selections are skipped and rapid changes produce one short-lived confirmation', async ({page}) => {
    await page.clock.install();
    const requests = [];
    await page.route('**/preferences', async (route) => {
        requests.push(Object.fromEntries(new URLSearchParams(route.request().postData())));
        await route.continue();
    });
    const root = await openDashboard(page);
    const progress = root.locator('.rrze-statistik-preferences-progress');
    await root.getByRole('tab', {name: 'visits', exact: true}).click();
    await page.clock.runFor(300);
    expect(requests).toHaveLength(0);
    await root.getByRole('tab', {name: 'hits', exact: true}).click();
    await root.getByRole('tab', {name: 'hosts', exact: true}).click();
    await root.getByLabel('Display type').selectOption('column');
    await root.getByRole('tab', {name: 'kbytes', exact: true}).click();
    await expect(progress).toHaveText('');
    expect(requests).toHaveLength(0);
    await page.clock.runFor(251);
    await expect(progress).toHaveText('Selection saved.');
    expect(requests).toEqual([{action: 'rrze_statistik_save_preferences', nonce: 'fixture-nonce', view: 'kbytes', displayType: 'column'}]);
    await page.clock.runFor(1501);
    await expect(progress).toHaveText('');
    await root.getByRole('tab', {name: 'kbytes', exact: true}).click();
    await root.getByLabel('Display type').selectOption('column');
    await page.clock.runFor(300);
    expect(requests).toHaveLength(1);
});

test('popular resources expose ranked tables and keyboard disclosures without narrow-layout overflow', async ({page}, testInfo) => {
    await page.setViewportSize({width: 410, height: 1200});
    const root = await openDashboard(page);
    await root.getByRole('tab', {name: 'Popular pages'}).click();
    const rankings = root.locator('.rrze-statistik-ranking');
    const sites = root.getByRole('table', {name: 'Sites', exact: true});
    await expect(rankings).toHaveCount(3);
    await expect(root.getByRole('table')).toHaveCount(1);
    await expect(sites.getByRole('columnheader')).toHaveText(['Sites', 'Requests']);
    await expect(sites.getByRole('rowheader')).toHaveCount(6);
    await expect(sites.getByRole('cell').first()).toHaveText('10,013');
    await expect(sites.getByRole('link', {name: 'Home page'})).toHaveAttribute('href', '/');
    await expect(sites.getByRole('cell').last()).toHaveText('0');
    await root.screenshot({path: testInfo.outputPath('top-pages-preview.png')});

    await page.setViewportSize({width: 320, height: 900});
    for (const index of [1, 2]) {
        const summary = rankings.nth(index).locator('summary');
        await summary.focus();
        await summary.press('Enter');
        await expect(rankings.nth(index).getByRole('table')).toBeVisible();
        await expect(summary).toBeFocused();
    }
    await expect.poll(() => page.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(320);
    const accessibility = await new AxeBuilder({page}).include('[data-statistik-panel="urls"]')
        .withTags(['wcag2a', 'wcag2aa', 'wcag21aa']).analyze();
    expect(accessibility.violations).toEqual([]);
    await rankings.nth(1).locator('summary').press('Enter');
    await expect(root.getByRole('table', {name: 'Media', exact: true})).toHaveCount(0);
});

test('single-widget preview, narrow tabs and enlarged layout', async ({page}, testInfo) => {
    await page.setViewportSize({width: 410, height: 1300});
    const root = await openDashboard(page);
    await root.screenshot({path: testInfo.outputPath('dashboard-preview.png')});
    await page.setViewportSize({width: 320, height: 900});
    for (const view of [...['visits', 'hits', 'hosts', 'files', 'kbytes'], 'Popular pages']) {
        await root.getByRole('tab', {name: view, exact: true}).click();
        await expect(root.getByRole('tabpanel')).toHaveCount(1);
        await expect.poll(() => page.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(320);
    }
    const accessibility = await new AxeBuilder({page}).include('[data-statistik-dashboard]').withTags(['wcag2a', 'wcag2aa', 'wcag21aa']).analyze();
    expect(accessibility.violations).toEqual([]);
    await page.setViewportSize({width: 1280, height: 900});
    await page.addStyleTag({content: 'html { zoom: 2; }'});
    await root.getByRole('tab', {name: 'visits', exact: true}).click();
    await expect.poll(() => page.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(1280);
});
