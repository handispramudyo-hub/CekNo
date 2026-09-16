import { expect, test } from '@playwright/test'

import { register } from './helpers'

test.describe('Flow 5: Profil & lupa kata sandi', () => {
  test('user dapat memperbarui profil dan kata sandi', async ({ page }) => {
    await register(page)

    await page.goto('/profile')
    await expect(page.getByRole('heading', { name: /E2E User|Profil/ })).toBeVisible()

    // Banner verifikasi muncul untuk user baru.
    await expect(page.getByTestId('verify-banner')).toBeVisible()

    await page.getByTestId('profile-edit-toggle').click()
    await page.getByTestId('profile-name').fill('E2E Profil Baru')
    await page.getByTestId('profile-phone').fill('081200001111')
    await page.getByTestId('profile-save').click()

    await expect(page.getByText(/Profil berhasil diperbarui/)).toBeVisible()
    await expect(page.getByRole('heading', { name: 'E2E Profil Baru' })).toBeVisible()

    // Ubah kata sandi dengan kata sandi saat ini yang benar.
    await page.getByTestId('current-password').fill('password123')
    await page.getByTestId('new-password').fill('newpassword123')
    await page.getByTestId('password-save').click()
    await expect(page.getByText(/Kata sandi berhasil diperbarui/)).toBeVisible()
  })

  test('halaman lupa kata sandi menerima email dan membalas netral', async ({ page }) => {
    await page.goto('/forgot-password')
    await page.getByTestId('email').fill('tidak-ada@cekno.id')
    await page.getByRole('button', { name: /Kirim tautan reset/ }).click()
    await expect(page.getByTestId('forgot-success')).toBeVisible()
  })

  test('halaman reset tanpa token menampilkan pesan tidak valid', async ({ page }) => {
    await page.goto('/reset-password')
    await expect(page.getByTestId('reset-invalid')).toBeVisible()
  })
})