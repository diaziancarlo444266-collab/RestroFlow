import { defineConfig } from '@playwright/test';

export default defineConfig({
    testDir: './tests/browser',
    workers: 1,
    use: { baseURL: 'http://127.0.0.1:8019', headless: true },
    webServer: {
        command: 'node tests/browser/serve.mjs',
        url: 'http://127.0.0.1:8019/login',
        reuseExistingServer: false,
        timeout: 60000,
    },
});
