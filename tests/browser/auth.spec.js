import { test, expect } from '@playwright/test';
import { readFileSync } from 'node:fs';

async function verifyEmail(page) {
    await expect(page).toHaveURL(/\/email-otp$/, { timeout: 20000 });
    const log = readFileSync('storage/logs/laravel.log', 'utf8');
    const codes = [...log.matchAll(/<strong>([0-9]{6})<\/strong>/g)];
    expect(codes.length).toBeGreaterThan(0);
    await page.getByLabel('Verification code', { exact: true }).fill(codes.at(-1)[1]);
    await page.getByRole('button', { name: 'Verify and sign in', exact: true }).click();
}

test('Fortify login, Inertia navigation, authenticated CRUD, and role controls', async ({ page }) => {
    test.setTimeout(150000);
    const errors = [];
    page.on('pageerror', error => errors.push(error.message));
    async function login(role) {
        await page.goto('/');
        await expect(page).toHaveURL(/\/login$/);
        await page.getByLabel('Email or username', { exact: true }).fill(`${role}@browser.test`);
        await page.getByLabel('Password', { exact: true }).fill('BrowserTestPassword123!');
        await page.getByRole('button', { name: 'Sign in', exact: true }).click();
        await expect(page.getByRole('heading', { name: role === 'viewer' ? 'Place an order and track it here.' : 'Everything your restaurant needs.' })).toBeVisible();
    }
    await login('admin');
    await page.getByRole('button', { name: 'Inventory', exact: true }).click();
    await page.getByRole('button', { name: 'Add Item', exact: true }).click();
    const name = `Browser Rice ${Date.now()}`;
    await page.getByLabel('Item Name', { exact: true }).fill(name);
    await page.getByLabel('Quantity', { exact: true }).fill('2');
    await page.getByLabel('Unit', { exact: true }).fill('kg');
    await page.getByLabel('Reorder At', { exact: true }).fill('3');
    await page.getByRole('button', { name: 'Save Item', exact: true }).click();
    await expect(page.getByRole('dialog')).toHaveCount(0);
    const row = page.getByRole('row').filter({ hasText: name });
    await expect(row).toContainText('Low Stock');
    await row.getByRole('button', { name: 'Edit Item', exact: true }).click();
    await page.getByLabel('Quantity', { exact: true }).fill('10');
    await page.getByRole('button', { name: 'Save Item', exact: true }).click();
    await expect(row).toContainText('Available');
    await page.getByRole('button', { name: 'Sign out', exact: true }).click();

    await login('staff');
    await page.getByRole('button', { name: 'Inventory', exact: true }).click();
    await expect(page.getByRole('button', { name: 'Add Item', exact: true })).toBeVisible();
    await expect(row.getByRole('button', { name: 'Edit Item', exact: true })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Delete Item', exact: true })).toHaveCount(0);
    await page.getByRole('button', { name: 'Sign out', exact: true }).click();

    await login('viewer');
    await expect(page.getByRole('button', { name: 'Inventory', exact: true })).toHaveCount(0);
    await expect(page.getByRole('button', { name: 'My Reservations', exact: true })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'My Recent Orders', exact: true })).toBeVisible();
    await page.getByRole('button', { name: 'New Order', exact: true }).click();
    await expect(page.getByLabel('Customer Name', { exact: true })).toHaveCount(0);
    await expect(page.getByLabel('Status', { exact: true })).toHaveCount(0);
    await page.getByLabel('Menu Item', { exact: true }).selectOption({ index: 1 });
    await page.getByRole('button', { name: 'Save Order', exact: true }).click();
    await expect(page.getByRole('dialog')).toHaveCount(0);
    await expect(page.getByRole('row').filter({ hasText: 'Viewer Browser Test' }).first()).toBeVisible();
    await page.getByRole('button', { name: 'My Reservations', exact: true }).click();
    await page.getByRole('button', { name: 'Add Reservation', exact: true }).click();
    await expect(page.getByLabel('Status', { exact: true })).toHaveCount(0);
    await page.getByLabel('Phone Number', { exact: true }).fill('09123456789');
    await page.getByLabel('Table', { exact: true }).selectOption({ index: 1 });
    await page.getByLabel('Time', { exact: true }).fill('18:30');
    await page.getByRole('button', { name: 'Save Reservation', exact: true }).click();
    await expect(page.getByRole('dialog')).toHaveCount(0);
    await expect(page.getByRole('row').filter({ hasText: 'Viewer Browser Test' }).first()).toContainText('Pending');
    await expect(page.getByRole('button', { name: 'Edit Reservation', exact: true })).toHaveCount(0);

    await page.getByRole('button', { name: 'Sign out', exact: true }).click();

    await login('admin2');
    await page.getByRole('button', { name: 'Inventory', exact: true }).click();
    page.once('dialog', dialog => dialog.accept());
    await row.getByRole('button', { name: 'Delete Item', exact: true }).click();
    await expect(row).toHaveCount(0);
    await expect(page.getByText('Item deleted successfully.', { exact: true })).toBeVisible();
    await page.getByRole('button', { name: 'Sign out', exact: true }).click();
    await expect(page).toHaveURL(/\/login$/);
    await page.getByRole('link', { name: 'Create an account' }).click();
    await page.getByLabel('Name', { exact: true }).fill('Registered Viewer');
    await page.getByLabel('Email', { exact: true }).fill(`new${Date.now()}@browser.test`);
    await page.getByLabel('Password (at least 8 characters)', { exact: true }).fill('BrowserTestPassword123!');
    await page.getByLabel('Confirm password', { exact: true }).fill('BrowserTestPassword123!');
    await page.getByRole('button', { name: 'Register', exact: true }).click();
    await verifyEmail(page);
    await expect(page.getByText('Registered Viewer', { exact: true })).toBeVisible();
    await expect(page.getByRole('button', { name: 'New Order', exact: true })).toBeVisible();
    await expect(page.getByRole('row').filter({ hasText: 'Viewer Browser Test' })).toHaveCount(0);
    await page.setViewportSize({ width: 390, height: 844 });
    await page.getByRole('button', { name: 'Toggle navigation', exact: true }).click();
    await expect(page.getByRole('button', { name: 'My Orders', exact: true })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Inventory', exact: true })).toHaveCount(0);
    expect(errors).toEqual([]);
});
