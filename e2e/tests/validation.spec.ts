import { expect, test } from '@playwright/test'

test.describe('Flow 5: Validasi & alur guest', () => {
  test('cari nomor kosong menampilkan peringatan', async ({ page }) => {
    await page.goto('/')
    await page.getByTestId('phone-input').fill('')
    await page.getByRole('button', { name: /Cek/i }).click()
    await expect(page.getByText(/Masukkan nomor minimal 9 digit/)).toBeVisible()
  })

  test('guest mengklik Lapor diarahkan ke login', async ({ page }) => {
    await page.goto('/')
    await page.getByTestId('phone-input').fill('081299887761')
    await page.getByRole('button', { name: /Cek/i }).click()
    await page.waitForURL(/\/numbers\/081299887761/)

    await page.getByRole('button', { name: /Lapor nomor ini/ }).click()
    await expect(page).toHaveURL(/\/login/, { timeout: 5_000 })
    await expect(page.getByText(/Masuk untuk melaporkan nomor ini/)).toBeVisible()
  })

  test('halaman profil tanpa login diarahkan ke login', async ({ page }) => {
    await page.goto('/profile')
    await expect(page).toHaveURL(/\/login/, { timeout: 10_000 })
    await expect(page.getByPlaceholder(/Kata sandi/)).toBeVisible()
  })
})