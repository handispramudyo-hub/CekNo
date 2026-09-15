import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import toast from 'react-hot-toast'

import { Button } from '../components/ui'

export function HomePage() {
  const navigate = useNavigate()
  const [phone, setPhone] = useState('')

  function submit(e: React.FormEvent) {
    e.preventDefault()
    const digits = phone.replace(/\D/g, '')
    if (digits.length < 9) {
      toast.error('Masukkan nomor minimal 9 digit')
      return
    }
    navigate(`/numbers/${encodeURIComponent(digits)}`)
  }

  return (
    <div className="pt-6">
      <section className="text-center">
        <div className="mx-auto flex h-20 w-20 items-center justify-center rounded-3xl bg-primary-container">
          <span className="icon text-5xl text-on-primary-container">shield_moon</span>
        </div>
        <h1 className="mt-4 text-2xl font-extrabold tracking-tight">
          Cek Nomor sebelum <span className="text-primary">Menghubungi</span>
        </h1>
        <p className="mt-2 text-sm leading-relaxed text-on-surface-variant">
          Ketahui reputasi nomor telepon berdasarkan laporan komunitas & analisis AI sebelum Anda menelepon atau
          menerima panggilan.
        </p>
      </section>

      <form onSubmit={submit} className="mt-8" data-testid="search-form">
        <label className="text-sm font-semibold" htmlFor="phone">
          Masukkan nomor telepon
        </label>
        <div className="mt-2 flex gap-2">
          <input
            id="phone"
            data-testid="phone-input"
            inputMode="tel"
            value={phone}
            onChange={(e) => setPhone(e.target.value)}
            placeholder="cth: 0812 3456 7890"
            className="h-12 min-w-0 flex-1 rounded-2xl border-2 border-outline-variant bg-surface px-4 text-base outline-none focus:border-primary"
          />
          <Button className="h-12 px-6" aria-label="Cek nomor">
            <span className="icon">search</span>
            Cek
          </Button>
        </div>
      </form>

      <section className="mt-6">
        <h2 className="text-sm font-semibold text-on-surface-variant">Coba cek nomor contoh</h2>
        <div className="mt-3 flex flex-wrap gap-2">
          {[
            ['0812 9988 7761', '081299887761'],
            ['0857 1234 5678', '085712345678'],
            ['0877 0001 2345', '087700012345'],
          ].map(([label, value]) => (
            <button
              key={value}
              type="button"
              onClick={() => navigate(`/numbers/${value}`)}
              className="rounded-full border border-outline-variant bg-surface-container px-4 py-2 text-sm font-medium transition active:bg-surface-container-high"
            >
              {label}
            </button>
          ))}
        </div>
      </section>

      <section className="mt-10 grid grid-cols-3 gap-3 text-center">
        {[
          ['groups', 'Komunitas', 'Laporan pengguna nyata'],
          ['psychology', 'AI', 'XGBoost + IndoBERT'],
          ['verified', 'Transparan', 'Skor & alasan terbuka'],
        ].map(([icon, t, d]) => (
          <div key={t} className="rounded-3xl bg-surface-container p-3">
            <span className="icon text-3xl text-primary">{icon}</span>
            <p className="mt-1.5 text-sm font-bold">{t}</p>
            <p className="mt-0.5 text-[11px] leading-snug text-on-surface-variant">{d}</p>
          </div>
        ))}
      </section>
    </div>
  )
}