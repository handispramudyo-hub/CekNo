import { useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { Link } from 'react-router-dom'
import toast from 'react-hot-toast'

import { Button, EmptyState, Spinner } from './ui'
import { api, apiError } from '../lib/api'
import { timeAgo } from '../lib/format'
import type { ContactContribution, ContributionsResponse } from '../lib/types'

const MAX_ROWS = 50

function StatusPill({ status }: { status: string }) {
  const cls =
    status === 'approved'
      ? 'bg-[#d5f2d5] text-[#2f6b2f]'
      : status === 'rejected' || status === 'withdrawn'
        ? 'bg-error-container text-on-error-container'
        : 'bg-[#f8e7a0] text-[#8a6d00]'
  return <span className={`rounded-full px-2.5 py-0.5 text-[11px] font-semibold ${cls}`}>{status}</span>
}

export function ContactContributionsPanel() {
  const queryClient = useQueryClient()
  const [rows, setRows] = useState<Array<{ phone: string; label: string }>>([{ phone: '', label: '' }])
  const [consented, setConsented] = useState(false)
  const [showForm, setShowForm] = useState(true)

  const query = useQuery({
    queryKey: ['my-contributions'],
    queryFn: async () => (await api.get<ContributionsResponse>('/user/contact-contributions')).data,
  })

  const consentGiven = !!query.data?.consent || consented

  const sync = useMutation({
    mutationFn: async () => {
      const contacts = rows
        .map((r) => ({ phone: r.phone.trim(), label: r.label.trim() }))
        .filter((r) => r.phone || r.label)
      if (!contacts.length) throw new Error('Tambahkan minimal satu kontak.')
      return (
        await api.post<{ created: number; duplicates: number; errors: unknown[] }>('/user/contact-contributions/sync', {
          consent_version: query.data?.consent_version ?? '1.0',
          contacts,
        })
      ).data
    },
    onSuccess: (data) => {
      setConsented(true)
      setRows([{ phone: '', label: '' }])
      setShowForm(false)
      queryClient.invalidateQueries({ queryKey: ['my-contributions'] })
      const msg = `${data.created} kontribusi baru${data.duplicates ? `, ${data.duplicates} duplikat` : ''}${
        data.errors.length ? `, ${data.errors.length} gagal` : ''
      }`
      toast.success(msg)
    },
    onError: (err) => toast.error(apiError(err)),
  })

  const withdraw = useMutation({
    mutationFn: async (id: number) => api.delete(`/user/contact-contributions/${id}`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['my-contributions'] })
      toast.success('Kontribusi ditarik')
    },
    onError: (err) => toast.error(apiError(err)),
  })

  function updateRow(i: number, key: 'phone' | 'label', value: string) {
    setRows((prev) => prev.map((r, idx) => (idx === i ? { ...r, [key]: value } : r)))
  }

  const items = query.data?.contributions?.data ?? []
  const canSubmit = rows.some((r) => r.phone.trim() && r.label.trim()) && rows.length <= MAX_ROWS

  return (
    <div className="grid gap-3">
      <p className="rounded-2xl bg-surface-container p-3.5 text-xs leading-relaxed text-on-surface-variant">
        Bagikan label kontak (misal &quot;Penipuan&quot;, &quot;Telemarketing&quot;) untuk memperkaya skor reputasi nomor.
        Kontribusi tidak menampilkan identitas Anda dan harus melalui moderasi sebelum terlihat publik. Lihat{' '}
        <Link to="/privacy" className="font-semibold text-primary">
          Kebijakan Privasi
        </Link>{' '}
        dan{' '}
        <Link to="/terms" className="font-semibold text-primary">
          Ketentuan Layanan
        </Link>
        .
      </p>

      {!consentGiven && (
        <div className="grid gap-2 rounded-2xl bg-surface-container p-4">
          <p className="text-sm font-semibold">Persetujuan data kontak</p>
          <p className="text-xs text-on-surface-variant">
            Saya menyetujui label kontak saya digunakan untuk identifikasi nomor dan ditautkan ke laporan komunitas (versi 1.0).
          </p>
          <Button data-testid="contribution-consent" className="mt-1 self-start" onClick={() => setConsented(true)}>
            Saya setuju
          </Button>
        </div>
      )}

      {consentGiven && !showForm && (
        <Button variant="outline" className="self-start h-9 px-3 text-xs" onClick={() => setShowForm(true)}>
          + Sinkronkan lagi
        </Button>
      )}

      {consentGiven && showForm && (
        <form
          data-testid="contribution-sync-form"
          onSubmit={(e) => {
            e.preventDefault()
            sync.mutate()
          }}
          className="grid gap-2 rounded-2xl bg-surface-container p-4"
        >
          <p className="text-sm font-semibold">Sinkronkan label kontak</p>
          <div className="grid gap-2">
            {rows.map((r, i) => (
              <div key={i} className="flex gap-2">
                <input
                  data-testid={`contact-phone-${i}`}
                  value={r.phone}
                  onChange={(e) => updateRow(i, 'phone', e.target.value)}
                  placeholder="Nomor telepon"
                  inputMode="tel"
                  className="h-11 min-w-0 flex-1 rounded-xl border-2 border-outline-variant bg-surface px-3 text-sm outline-none focus:border-primary"
                />
                <input
                  data-testid={`contact-label-${i}`}
                  value={r.label}
                  onChange={(e) => updateRow(i, 'label', e.target.value)}
                  placeholder="Label (cth: Penipuan)"
                  className="h-11 min-w-0 flex-1 rounded-xl border-2 border-outline-variant bg-surface px-3 text-sm outline-none focus:border-primary"
                />
                {rows.length > 1 && (
                  <button
                    type="button"
                    aria-label="Hapus baris"
                    onClick={() => setRows((prev) => prev.filter((_, idx) => idx !== i))}
                    className="icon self-center text-on-surface-variant"
                  >
                    close
                  </button>
                )}
              </div>
            ))}
          </div>
          <div className="flex gap-2">
            {rows.length < MAX_ROWS && (
              <Button type="button" variant="outline" className="h-10 px-4 text-xs" onClick={() => setRows((prev) => [...prev, { phone: '', label: '' }])}>
                + Tambah kontak
              </Button>
            )}
            <Button data-testid="contribution-sync" disabled={!canSubmit || sync.isPending}>
              {sync.isPending ? 'Mengirim…' : 'Sinkronkan'}
            </Button>
          </div>
        </form>
      )}

      {query.isLoading ? (
        <Spinner label="Memuat kontribusi…" />
      ) : items.length ? (
        <ul className="grid gap-2">
          {items.map((c: ContactContribution) => (
            <li key={c.id} className="flex flex-wrap items-center gap-2 rounded-2xl bg-surface-container p-3.5 text-sm">
              <span className="font-semibold">{c.label}</span>
              <span className="text-on-surface-variant">{c.phone_number?.normalized_number ?? '—'}</span>
              <StatusPill status={c.status} />
              {c.status === 'pending' || c.status === 'approved' ? (
                <button
                  data-testid={`contribution-withdraw-${c.id}`}
                  onClick={() => withdraw.mutate(c.id)}
                  className="ml-auto text-xs font-semibold text-error"
                >
                  Tarik
                </button>
              ) : (
                <span className="ml-auto text-xs text-on-surface-variant">{timeAgo(c.created_at)}</span>
              )}
            </li>
          ))}
        </ul>
      ) : (
        <EmptyState icon="contacts" title="Belum ada kontribusi" hint="Setujui dan sinkronkan label kontak untuk mulai berkontribusi." />
      )}
    </div>
  )
}