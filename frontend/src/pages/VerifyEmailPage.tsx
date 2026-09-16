import { useEffect, useRef, useState } from 'react'
import { Link, useSearchParams } from 'react-router-dom'
import { useQueryClient } from '@tanstack/react-query'

import { api, apiError } from '../lib/api'
import { Spinner } from '../components/ui'

export function VerifyEmailPage() {
  const [params] = useSearchParams()
  const queryClient = useQueryClient()

  const id = params.get('id') ?? ''
  const hash = params.get('hash') ?? ''
  const missing = !id || !hash

  const [status, setStatus] = useState<'loading' | 'ok' | 'error'>(missing ? 'error' : 'loading')
  const [message, setMessage] = useState(missing ? 'Tautan verifikasi tidak lengkap.' : '')
  const ran = useRef(false)

  useEffect(() => {
    if (missing || ran.current) return
    ran.current = true

    api
      .post<{ message: string }>('/auth/email/verify', { id: Number(id), hash })
      .then((res) => {
        setStatus('ok')
        setMessage(res.data.message)
        queryClient.invalidateQueries({ queryKey: ['me'] })
      })
      .catch((err) => {
        setStatus('error')
        setMessage(apiError(err))
      })
  }, [missing, id, hash, queryClient])

  if (status === 'loading') return <Spinner label="Memverifikasi email…" />

  return (
    <div className="flex flex-col gap-6 pt-10 text-center">
      <span
        className={`icon mx-auto flex h-16 w-16 items-center justify-center rounded-3xl text-4xl ${
          status === 'ok' ? 'bg-primary-container text-on-primary-container' : 'bg-error-container text-on-error-container'
        }`}
      >
        {status === 'ok' ? 'mark_email_read' : 'error'}
      </span>
      <div>
        <h1 className="text-2xl font-extrabold">{status === 'ok' ? 'Email terverifikasi' : 'Verifikasi gagal'}</h1>
        <p data-testid="verify-message" className="mt-1 text-sm text-on-surface-variant">
          {message}
        </p>
      </div>
      <Link to="/profile" className="font-semibold text-primary underline">
        Buka profil
      </Link>
    </div>
  )
}