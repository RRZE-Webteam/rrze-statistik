const {defineConfig, devices} = require('@playwright/test');

module.exports = defineConfig({
    testDir: './tests/browser',
    testMatch: '**/*.spec.cjs',
    timeout: 30000,
    use: {baseURL: 'http://127.0.0.1:8765', screenshot: 'only-on-failure', trace: 'retain-on-failure'},
    projects: [
        {name: 'chromium', use: {...devices['Desktop Chrome']}},
        {name: 'firefox', use: {...devices['Desktop Firefox']}},
        {name: 'webkit', use: {...devices['Desktop Safari']}},
    ],
    webServer: {
        command: 'node tests/browser/server.cjs', url: 'http://127.0.0.1:8765', reuseExistingServer: false,
    },
});
