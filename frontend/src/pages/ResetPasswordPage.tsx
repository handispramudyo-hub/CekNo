import { useState } from 'react'
import { Link, useSearchParams } from 'react-router-dom'

import { Button } from '../components/ui'
import { useAuth } from '../hooks/useAuth'

export function ResetPasswordPage() {
  const [params] = useSearchParams()
  const { resetPassword } = useAuth()
  const token = params.get('token') ?? ''
  const email = params.get('email') ?? ''
  const [password, setPassword] = useState('')
  const [done, setDone] = useState(false)

  const missing = !token || !email

  function submit(e: React.FormEvent) {
    e.preventDefault()
    resetPassword.mutate({ token, email, password }, { onSuccess: () => setDone(true) })
  }

  return (
    <div className="flex flex-col gap-6 pt-8">
      <header className="text-center">
        <span className="icon mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-primary-container text-4xl text-on-primary-container">
          password
        </span>
        <h1 className="mt-3 text-2xl font-extrabold">Buat kata sandi baru</h1>
      </header>

      {missing ? (
        <div data-testid="reset-invalid" className="rounded-3xl bg-error-container p-5 text-center text-on-error-container">
          <p className="font-semibold">Tautan tidak lengkap</p>
          <p className="mt-1 text-sm">Tautan reset tidak valid. Minta tautan baru dari halaman lupa kata sandi.</p>
        </div>
      ) : done ? (
        <div data-testid="reset-success" className="rounded-3xl bg-secondary-container p-5 text-center text-on-secondary-container">
          <p className="font-semibold">Kata sandi berhasil direset</p>
          <p className="mt-1 text-sm">Silakan masuk dengan kata sandi baru.</p>
        </div>
      ) : (
        <form onSubmit={submit} className="grid gap-3">
          <p className="text-center text-sm text-on-surface-variant">
            Untuk akun <span className="font-semibold">{email}</span>
          </p>
          <input
            data-testid="password"
            required
            type="password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            placeholder="Kata sandi baru (min. 8)"
            autoComplete="new-password"
            minLength={8}
            className="h-12 rounded-2xl border-2 border-outline-variant bg-surface px-4 outline-none focus:border-primary"
          />
          <Button className="w-full" disabled={resetPassword.isPending}>
            {resetPassword.isPending ? 'Menyimpan…' : 'Simpan kata sandi baru'}
          </Button>
        </form>
      )}

      <p className="text-center text-sm text-on-surface-variant">
        <Link to="/login" className="font-semibold text-primary">
          Kembali ke halaman masuk
        </Link>
      </p>
    </div>
  )
}