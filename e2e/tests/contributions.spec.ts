import { expect, test } from '@playwright/test'

import { register } from './helpers'

test.describe('Flow 6: Kontribusi kontak', () => {
  test('user menyetujui, menyinkronkan, dan menarik kontribusi', async ({ page }) => {
    await register(page)
    await page.goto('/profile')

    await page.getByRole('button', { name: /Kontribusi/ }).click()

    // Consent card tampil sebelum persetujuan.
    await expect(page.getByTestId('contribution-consent')).toBeVisible()
    await page.getByTestId('contribution-consent').click()

    // Form sinkronisasi muncul setelah consent.
    await expect(page.getByTestId('contribution-sync-form')).toBeVisible()
    await page.getByTestId('contact-phone-0').fill('0877 000 0001')
    await page.getByTestId('contact-label-0').fill('Penipuan')
    await page.getByTestId('contribution-sync').click()

    await expect(page.getByText(/kontribusi baru/)).toBeVisible()
    await expect(page.getByText('Penipuan', { exact: true })).toBeVisible()
    await expect(page.getByText(/pending|approved/)).toBeVisible()

    // Tarik kembali kontribusi.
    const item = page.getByText('Penipuan', { exact: true }).locator('xpath=ancestor::li')
    await item.getByRole('button', { name: 'Tarik' }).click()
    await expect(page.getByText(/Kontribusi ditarik/)).toBeVisible()
    await expect(page.getByText(/withdrawn/)).toBeVisible()
  })
})