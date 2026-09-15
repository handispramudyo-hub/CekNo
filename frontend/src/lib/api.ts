import axios from 'axios'

const TOKEN_KEY = 'cekno_token'

function cookies(): Record<string, string> {
  return Object.fromEntries(
    document.cookie.split(';').map((c) => c.trim().split('=')).filter(([k]) => k).map(([k, v]) => [k, decodeURIComponent(v ?? '')]),
  )
}

function guestId(): string | null {
  let id = cookies()['guest_id'] ?? null
  if (!id) {
    id = crypto.randomUUID()
    document.cookie = `guest_id=${encodeURIComponent(id)}; path=/; max-age=${60 * 60 * 24 * 90}; SameSite=Lax`
  }
  return id
}

export const tokenStore = {
  get: () => localStorage.getItem(TOKEN_KEY),
  set: (token?: string | null) => {
    if (token) localStorage.setItem(TOKEN_KEY, token)
    else localStorage.removeItem(TOKEN_KEY)
  },
}

export const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL ?? 'http://127.0.0.1:8000/api',
  headers: { Accept: 'application/json' },
})

api.interceptors.request.use((cfg) => {
  const token = tokenStore.get()
  if (token) cfg.headers.Authorization = `Bearer ${token}`
  if (!cfg.headers['X-Guest-Id']) cfg.headers['X-Guest-Id'] = guestId()
  return cfg
})

api.interceptors.response.use(
  (res) => res,
  (err: unknown) => {
    if (axios.isAxiosError(err) && err.response?.status === 401) {
      tokenStore.set(null)
    }
    return Promise.reject(err)
  },
)

export function apiError(err: unknown): string {
  if (axios.isAxiosError(err)) {
    const data = err.response?.data as { message?: string; errors?: Record<string, string[]> } | undefined
    if (data?.errors) {
      const [first] = Object.values(data.errors)
      if (first?.length) return first[0]
    }
    if (data?.message) return data.message
  }
  return 'Terjadi kesalahan. Coba lagi.'
}