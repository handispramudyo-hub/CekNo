import { expect, test } from '@playwright/test'

import { ADMIN_EMAIL, ADMIN_PASSWORD, login } from './helpers'

test.describe('Flow 3: Moderasi admin', () => {
  test('login admin dan setujui satu laporan pending', async ({ page }) => {
    await login(page, ADMIN_EMAIL, ADMIN_PASSWORD)

    await page.goto('/admin')
    await expect(page.getByText('Panel Admin')).toBeVisible()

    await page.goto('/admin/moderation')
    await expect(page.getByRole('button', { name: 'Laporan' })).toBeVisible()

    // Tunggu daftar selesai dimuat: item modals atau pesan kosong.
    const pendingItem = page.locator('li:has-text("Setujui")').first()
    await Promise.race([
      pendingItem.waitFor({ state: 'visible', timeout: 15_000 }),
      page.getByText(/Tidak ada Laporan pending|Kerja bagus/).waitFor({ state: 'visible', timeout: 15_000 }),
    ])

    const count = await pendingItem.count()

    if (count > 0) {
      await pendingItem.getByRole('button', { name: 'Setujui' }).click()
      await expect(page.getByText(/Moderasi selesai/)).toBeVisible()
    } else {
      await expect(page.getByText(/Tidak ada Laporan pending|Kerja bagus/)).toBeVisible()
    }
  })

  test('menu Moderation berpindah tab Reviews', async ({ page }) => {
    await login(page, ADMIN_EMAIL, ADMIN_PASSWORD)
    await page.goto('/admin/moderation')
    await page.getByRole('button', { name: 'Ulasan' }).click()
    await expect(page.getByRole('button', { name: 'Ulasan' })).toHaveClass(/bg-primary/)
  })
})