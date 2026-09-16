import { useState } from 'react'
import { Link, useNavigate, useSearchParams } from 'react-router-dom'

import { Button } from '../components/ui'
import { useAuth } from '../hooks/useAuth'

export function AuthPage({ mode }: { mode: 'login' | 'register' }) {
  const navigate = useNavigate()
  const [params] = useSearchParams()
  const { login, register, user } = useAuth()
  const [name, setName] = useState('')
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')

  if (user) {
    setTimeout(() => navigate('/', { replace: true }), 0)
    return null
  }

  const isLogin = mode === 'login'
  const pending = isLogin ? login.isPending : register.isPending

  function submit(e: React.FormEvent) {
    e.preventDefault()
    if (isLogin) {
      login.mutate({ email, password })
    } else {
      register.mutate({ name, email, password })
    }
  }

  return (
    <div className="flex flex-col gap-6 pt-8">
      <header className="text-center">
        <span className="icon mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-primary-container text-4xl text-on-primary-container">
          {isLogin ? 'person' : 'person_add'}
        </span>
        <h1 className="mt-3 text-2xl font-extrabold">{isLogin ? 'Selamat datang kembali' : 'Buat akun'}</h1>
        <p className="mt-1 text-sm text-on-surface-variant">
          {isLogin ? 'Masuk untuk melaporkan & menulis ulasan' : 'Bergabung untuk melindungi sesama pengguna'}
        </p>
      </header>

      <form onSubmit={submit} className="grid gap-3">
        {!isLogin && (
          <input
            data-testid="name"
            required
            value={name}
            onChange={(e) => setName(e.target.value)}
            placeholder="Nama lengkap"
            autoComplete="name"
            className="h-12 rounded-2xl border-2 border-outline-variant bg-surface px-4 outline-none focus:border-primary"
          />
        )}
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
        <input
          data-testid="password"
          required
          type="password"
          value={password}
          onChange={(e) => setPassword(e.target.value)}
          placeholder="Kata sandi (min. 8)"
          autoComplete={isLogin ? 'current-password' : 'new-password'}
          minLength={8}
          className="h-12 rounded-2xl border-2 border-outline-variant bg-surface px-4 outline-none focus:border-primary"
        />
        {isLogin && (
          <Link to="/forgot-password" className="self-end text-sm font-semibold text-primary">
            Lupa kata sandi?
          </Link>
        )}
        <Button className="w-full" disabled={pending}>
          {pending ? 'Memproses…' : isLogin ? 'Masuk' : 'Daftar'}
        </Button>
      </form>

      <p className="text-center text-sm text-on-surface-variant">
        {isLogin ? 'Belum punya akun?' : 'Sudah punya akun?'}{' '}
        <Link
          to={isLogin ? `/register${params.get('next') ? `?next=${params.get('next')}` : ''}` : '/login'}
          className="font-semibold text-primary"
        >
          {isLogin ? 'Daftar' : 'Masuk'}
        </Link>
      </p>
    </div>
  )
}