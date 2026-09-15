import { expect, test } from '@playwright/test'

import { ADMIN_EMAIL, ADMIN_PASSWORD, login } from './helpers'

test.describe('Flow 4: Panel admin & analitik', () => {
  test('dashboard menampilkan ringkasan', async ({ page }) => {
    await login(page, ADMIN_EMAIL, ADMIN_PASSWORD)
    await page.goto('/admin')
    await expect(page.getByText('Ringkasan')).toBeVisible()
    await expect(page.getByText('Laporan dipending')).toBeVisible()
    await expect(page.getByText('Total nomor terindeks')).toBeVisible()
  })

  test('halaman analitik memuat grafik distribusi', async ({ page }) => {
    await login(page, ADMIN_EMAIL, ADMIN_PASSWORD)
    await page.goto('/admin/analytics')
    await expect(page.getByText('Distribusi tingkat risiko')).toBeVisible({ timeout: 20_000 })
  })

  test('daftar nomor admin menampilkan level risiko', async ({ page }) => {
    await login(page, ADMIN_EMAIL, ADMIN_PASSWORD)
    await page.goto('/admin/numbers')
    await expect(page.getByText(/Total nomor/)).toBeVisible()
  })
})