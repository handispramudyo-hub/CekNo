import { expect, test } from '@playwright/test'

import { randomEmail } from './helpers'

test.describe('Flow 2: Daftar & mengirim laporan', () => {
  test('register lalu kirim laporan untuk nomor', async ({ page }) => {
    const email = randomEmail()

    await page.goto('/register')
    await page.getByTestId('name').fill('E2E User')
    await page.getByTestId('email').fill(email)
    await page.getByTestId('password').fill('password123')
    await page.getByRole('button', { name: 'Daftar' }).click()

    await page.waitForURL('/', { timeout: 15_000 })
    await expect(page.getByRole('heading', { name: /Cek Nomor before|Cek Nomor sebelum/ })).toBeVisible()

    await page.goto('/report?phone=081298765432')
    await page.getByTestId('report-phone').fill('081298765432')
    await page.getByRole('button', { name: 'Penipuan' }).click()
    await page.getByTestId('report-desc').fill(`Menghubungi tengah malam mengaku bank meminta kode OTP transfer (akhir pekan ${Date.now()}).`)

    await page.getByRole('button', { name: /Kirim laporan/ }).click()

    await expect(page).toHaveURL(/\/numbers\/081298765432/)
    await expect(page.getByText(/Laporan terkirim/)).toBeVisible()
  })

  test('laporan tanpa deskripsi valid ditolak', async ({ page }) => {
    await page.goto('/register')
    await page.getByTestId('name').fill('E2E User')
    await page.getByTestId('email').fill(randomEmail())
    await page.getByTestId('password').fill('password123')
    await page.getByRole('button', { name: 'Daftar' }).click()
    await page.waitForURL('/', { timeout: 15_000 })

    await page.goto('/report?phone=081298765432')
    await page.getByTestId('report-desc').fill('singkat')
    await page.getByRole('button', { name: /Kirim laporan/ }).click()
    await expect(page.getByText(/Lengkapi nomor|deskripsi/)).toBeVisible()
  })
})