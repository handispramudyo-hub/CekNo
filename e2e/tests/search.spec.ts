import { expect, test } from '@playwright/test'

test.describe('Flow 1: Cek nomor', () => {
  test('mencari nomor menampilkan skor risiko', async ({ page }) => {
    await page.goto('/')
    await expect(page).toHaveTitle(/CekNO/)

    await page.getByTestId('phone-input').fill('081299887761')
    await page.getByRole('button', { name: /Cek/i }).click()

    await expect(page).toHaveURL(/\/numbers\/081299887761/)
    await expect(page.getByText(/Risiko (Tinggi|Sedang|Rendah)/)).toBeVisible()
    await expect(page.getByText(/SKOR RISIKO/)).toBeVisible()
    await expect(page.getByRole('button', { name: /Lapor nomor ini/ })).toBeVisible()
  })

  test('chip nomor contoh di beranda membuka halaman nomor', async ({ page }) => {
    await page.goto('/')
    await page.getByRole('button', { name: '0812 9988 7761' }).click()
    await expect(page).toHaveURL(/\/numbers\/081299887761/)
  })
})