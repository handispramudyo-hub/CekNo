import { useState } from 'react'
import { useNavigate, useSearchParams } from 'react-router-dom'
import toast from 'react-hot-toast'

import { Button, Card } from '../components/ui'
import { useAuth } from '../hooks/useAuth'
import { api, apiError } from '../lib/api'

const CATEGORIES: { value: string; label: string; icon: string }[] = [
  { value: 'fraud', label: 'Penipuan', icon: 'gpp_bad' },
  { value: 'phishing', label: 'Phishing/OTP', icon: 'vpn_key_off' },
  { value: 'spam', label: 'Spam', icon: 'mark_email_unread' },
  { value: 'loan', label: 'Pinjol', icon: 'payments' },
  { value: 'telemarketing', label: 'Telemarketing', icon: 'call' },
  { value: 'harassment', label: 'Penghinaan', icon: 'mood_bad' },
  { value: 'other', label: 'Lainnya', icon: 'help_outline' },
]

export function ReportPage() {
  const [params] = useSearchParams()
  const navigate = useNavigate()
  const { user } = useAuth()
  const initialPhone = (params.get('phone') ?? '').replace(/\D/g, '')
  const [phone, setPhone] = useState(initialPhone)
  const [category, setCategory] = useState('fraud')
  const [description, setDescription] = useState('')
  const [sending, setSending] = useState(false)

  function submit(e: React.FormEvent) {
    e.preventDefault()
    if (!user) {
      toast('Masuk untuk mengirim laporan — melindungi dari penyalahgunaan.')
      setTimeout(() => navigate('/login?next=/report'), 600)
      return
    }
    if (phone.replace(/\D/g, '').length < 9 || description.trim().length < 10) {
      toast.error('Lengkapi nomor (9+ digit) dan deskripsi (min 10 karakter)')
      return
    }
    setSending(true)
    api
      .post(`/numbers/${phone.replace(/\D/g, '')}/reports`, { category, description })
      .then(() => {
        toast.success('Laporan terkirim & sedang ditinjau')
        navigate(`/numbers/${phone.replace(/\D/g, '')}`)
      })
      .catch((err) => toast.error(apiError(err)))
      .finally(() => setSending(false))
  }

  return (
    <div className="flex flex-col gap-4">
      <header>
        <h1 className="text-xl font-extrabold">Lapor nomor mencurigakan</h1>
        <p className="mt-1 text-sm text-on-surface-variant">
          Laporan akan ditinjau moderator sebelum tampil. Setiap laporan membantu orang lain.
        </p>
      </header>

      <form onSubmit={submit} className="grid gap-4">
        <Card>
          <label className="text-sm font-semibold" htmlFor="report-phone">
            Nomor telepon
          </label>
          <input
            id="report-phone"
            inputMode="tel"
            value={phone}
            onChange={(e) => setPhone(e.target.value)}
            placeholder="cth: 0812 3456 7890"
            className="mt-1 h-12 w-full rounded-2xl border-2 border-outline-variant bg-surface px-4 text-base outline-none focus:border-primary"
          />
        </Card>

        <Card>
          <p className="mb-3 text-sm font-semibold">Kategori laporan</p>
          <div className="grid grid-cols-2 gap-2">
            {CATEGORIES.map((c) => (
              <button
                key={c.value}
                type="button"
                onClick={() => setCategory(c.value)}
                className={`flex items-center gap-2 rounded-2xl border-2 px-3 py-3 text-sm font-medium transition ${
                  category === c.value
                    ? 'border-primary bg-primary-container text-on-primary-container'
                    : 'border-outline-variant bg-surface'
                }`}
              >
                <span className="icon">{c.icon}</span>
                {c.label}
              </button>
            ))}
          </div>
        </Card>

        <Card>
          <label className="text-sm font-semibold" htmlFor="report-desc">
            Deskripsi (min. 10 karakter)
          </label>
          <textarea
            id="report-desc"
            rows={5}
            value={description}
            onChange={(e) => setDescription(e.target.value)}
            placeholder="Contoh: Menghubungi pada tengah malam mengaku dari bank dan meminta kode OTP…"
            className="mt-1 w-full rounded-2xl border-2 border-outline-variant bg-surface p-3 text-sm outline-none focus:border-primary"
          />
        </Card>

        <Button className="h-13 w-full" disabled={sending}>
          {sending ? 'Mengirim…' : 'Kirim laporan'}
        </Button>
      </form>
    </div>
  )
}