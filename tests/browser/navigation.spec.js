import { test, expect } from '@playwright/test';

test('Back and Forward recheck authentication after login and logout', async ({ page }) => {
    await page.goto('/register');
    await page.getByRole('link', { name: 'Sign in', exact: true }).click();
    await page.getByLabel('Email or username').fill('admin@browser.test');
    await page.getByLabel('Password', { exact: true }).fill('BrowserTestPassword123!');
    await page.getByRole('button', { name: 'Sign in', exact: true }).click();
    await expect(page.getByRole('button', { name: 'Sign out' })).toBeVisible();
    await page.goBack();
    await expect(page).toHaveURL('http://127.0.0.1:8019/');
    await expect(page.getByRole('button', { name: 'Sign out' })).toBeVisible();
    await expect(page.getByLabel('Password', { exact: true })).toHaveCount(0);
    await page.goto('/register');
    await expect(page).toHaveURL('http://127.0.0.1:8019/');
    await page.getByRole('button', { name: 'Sign out' }).click();
    await expect(page).toHaveURL(/\/login$/);
    await page.goBack();
    await expect(page).toHaveURL(/\/login$/);
    await expect(page.getByRole('button', { name: 'Sign in', exact: true })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Sign out' })).toHaveCount(0);
    await page.goForward();
    await expect(page).toHaveURL(/\/login$/);
    expect((await page.request.get('/api/orders', { headers: { Accept: 'application/json' } })).status()).toBe(401);
});
