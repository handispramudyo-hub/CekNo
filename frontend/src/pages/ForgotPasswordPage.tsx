import { useState } from 'react'
import { Link } from 'react-router-dom'

import { Button } from '../components/ui'
import { useAuth } from '../hooks/useAuth'

export function ForgotPasswordPage() {
  const { forgotPassword } = useAuth()
  const [email, setEmail] = useState('')
  const [sent, setSent] = useState(false)

  function submit(e: React.FormEvent) {
    e.preventDefault()
    forgotPassword.mutate({ email }, { onSuccess: () => setSent(true) })
  }

  return (
    <div className="flex flex-col gap-6 pt-8">
      <header className="text-center">
        <span className="icon mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-primary-container text-4xl text-on-primary-container">
          lock_reset
        </span>
        <h1 className="mt-3 text-2xl font-extrabold">Lupa kata sandi</h1>
        <p className="mt-1 text-sm text-on-surface-variant">
          Masukkan email akunmu, kami akan mengirim tautan untuk membuat kata sandi baru.
        </p>
      </header>

      {sent ? (
        <div data-testid="forgot-success" className="rounded-3xl bg-secondary-container p-5 text-center text-on-secondary-container">
          <p className="font-semibold">Permintaan diterima</p>
          <p className="mt-1 text-sm">
            Jika <span className="font-semibold">{email}</span> terdaftar, tautan reset kata sandi sudah dikirim. Cek kotak masuk
            (atau log server pada mode pengembangan).
          </p>
        </div>
      ) : (
        <form onSubmit={submit} className="grid gap-3">
          <input
            data-testid="email"
            required
            type="email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            placeholder="Email"
            autoComplete="email"
            className="h-12 rounded-2xl border-2 border-outline-variant bg-surface px-4 outline-none focus:border-primary"
          />
          <Button className="w-full" disabled={forgotPassword.isPending}>
            {forgotPassword.isPending ? 'Mengirim…' : 'Kirim tautan reset'}
          </Button>
        </form>
      )}

      <p className="text-center text-sm text-on-surface-variant">
        Ingat kata sandi?{' '}
        <Link to="/login" className="font-semibold text-primary">
          Masuk
        </Link>
      </p>
    </div>
  )
}