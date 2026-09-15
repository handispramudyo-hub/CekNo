import type { Page } from '@playwright/test'

export const ADMIN_EMAIL = 'admin@cekno.id'
export const ADMIN_PASSWORD = 'password'

export function randomEmail(): string {
  return `e2e_${Date.now()}_${Math.floor(Math.random() * 1e6)}@cekno.id`
}

export async function login(page: Page, email: string, password: string): Promise<void> {
  // Buka halaman app dulu agar origin tersedia sebelum akses localStorage
  await page.goto('/')
  await page.evaluate(() => localStorage.removeItem('cekno_token'))
  await page.goto('/login')
  await page.getByTestId('email') .fill(email)
  await page.getByTestId('password').fill(password)
  await page.getByRole('button', { name: 'Masuk', exact: true }).click()
  await page.waitForURL('/', { timeout: 15_000 })
}

export async function register(page: Page): Promise<string> {
  const email = randomEmail()
  await page.goto('/register')
  await page.getByTestId('name').fill('E2E User')
  await page.getByTestId('email').fill(email)
  await page.getByTestId('password').fill('password123')
  await page.getByRole('button', { name: 'Daftar' }).click()
  await page.waitForURL('/', { timeout: 15_000 })
  return email
}