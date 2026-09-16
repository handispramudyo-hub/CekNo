import { useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import { Link, Navigate } from 'react-router-dom'

import { Button, EmptyState, Spinner } from '../components/ui'
import { ContactContributionsPanel } from '../components/ContactContributionsPanel'
import { useAuth } from '../hooks/useAuth'
import { api } from '../lib/api'
import { formatPhone, phoneRel, timeAgo } from '../lib/format'
import type { ContributionsResponse, Report, Review, TagChip } from '../lib/types'

interface Page<T> {
  data: T[]
  total?: number
}

function StatusPill({ status }: { status: string }) {
  const cls =
    status === 'approved' ? 'bg-[#d5f2d5] text-[#2f6b2f]' : status === 'rejected' ? 'bg-error-container text-on-error-container' : 'bg-[#f8e7a0] text-[#8a6d00]'
  return <span className={`rounded-full px-2.5 py-0.5 text-[11px] font-semibold ${cls}`}>{status}</span>
}

export function ProfilePage() {
  const { user, isAdmin, logout, loading, updateProfile, updatePassword, resendVerification } = useAuth()
  const [tab, setTab] = useState<'reports' | 'reviews' | 'tags' | 'contributions'>('reports')
  const [editing, setEditing] = useState(false)
  const [name, setName] = useState('')
  const [phone, setPhone] = useState('')
  const [currentPassword, setCurrentPassword] = useState('')
  const [newPassword, setNewPassword] = useState('')

  const reports = useQuery({ queryKey: ['my-reports'], queryFn: async () => (await api.get<Page<Report>>('/user/reports')).data })
  const reviews = useQuery({ queryKey: ['my-reviews'], queryFn: async () => (await api.get<Page<Review>>('/user/reviews')).data })
  const tags = useQuery({ queryKey: ['my-tags'], queryFn: async () => (await api.get<Page<TagChip>>('/user/tags')).data })
  const contributions = useQuery({ queryKey: ['my-contributions'], queryFn: async () => (await api.get<ContributionsResponse>('/user/contact-contributions')).data })

  if (!user) {
    if (!loading) return <Navigate to="/login?next=/profile" replace />
    return <Spinner label="Memuat profil…" />
  }

  function startEdit() {
    setName(user!.name)
    setPhone(user!.phone ?? '')
    setEditing(true)
  }

  function saveProfile(e: React.FormEvent) {
    e.preventDefault()
    updateProfile.mutate({ name, phone: phone || null }, { onSuccess: () => setEditing(false) })
  }

  function savePassword(e: React.FormEvent) {
    e.preventDefault()
    updatePassword.mutate(
      { current_password: currentPassword, password: newPassword },
      {
        onSuccess: () => {
          setCurrentPassword('')
          setNewPassword('')
        },
      },
    )
  }

  const tabs = [
    ['reports', 'Laporan', reports.data?.total ?? reports.data?.data.length ?? 0],
    ['reviews', 'Ulasan', reviews.data?.total ?? reviews.data?.data.length ?? 0],
    ['tags', 'Label', tags.data?.total ?? tags.data?.data.length ?? 0],
    ['contributions', 'Kontribusi', contributions.data?.contributions?.data.length ?? 0],
  ] as const

  return (
    <div className="flex flex-col gap-5">
      <section className="flex items-center gap-4 rounded-3xl bg-surface-container p-5">
        <span className="icon flex h-14 w-14 items-center justify-center rounded-2xl bg-primary-container text-3xl text-on-primary-container">
          person
        </span>
        <div className="min-w-0">
          <h1 className="truncate text-lg font-extrabold">{user.name}</h1>
          <p className="truncate text-sm text-on-surface-variant">{user.email}</p>
          <p className="mt-0.5 text-xs font-medium capitalize text-primary">{user.role}</p>
        </div>
        <Button variant="outline" className="ml-auto h-9 px-3 text-xs" onClick={() => logout.mutate()}>
          Keluar
        </Button>
      </section>

      {!user.email_verified_at && (
        <section data-testid="verify-banner" className="flex flex-wrap items-center gap-3 rounded-3xl bg-[#f8e7a0] p-4 text-[#8a6d00]">
          <span className="icon">mark_email_unread</span>
          <p className="text-sm font-semibold">Email belum diverifikasi.</p>
          <Button
            variant="outline"
            className="ml-auto h-9 border-[#8a6d00] px-3 text-xs text-[#8a6d00]"
            disabled={resendVerification.isPending}
            onClick={() => resendVerification.mutate()}
          >
            {resendVerification.isPending ? 'Mengirim…' : 'Kirim ulang'}
          </Button>
        </section>
      )}

      {isAdmin && (
        <Link to="/admin" className="flex items-center gap-3 rounded-3xl bg-secondary-container p-4 font-semibold text-on-secondary-container">
          <span className="icon">admin_panel_settings</span> Panel Admin
          <span className="icon ml-auto">arrow_forward</span>
        </Link>
      )}

      <section>
        <div className="flex items-center justify-between">
          <h2 className="text-sm font-bold uppercase text-on-surface-variant">Pengaturan akun</h2>
          {!editing && (
            <Button data-testid="profile-edit-toggle" variant="ghost" className="h-8 px-2 text-xs" onClick={startEdit}>
              Edit profil
            </Button>
          )}
        </div>

        {editing ? (
          <form onSubmit={saveProfile} className="mt-2 grid gap-3 rounded-3xl bg-surface-container p-4">
            <input
              data-testid="profile-name"
              required
              value={name}
              onChange={(e) => setName(e.target.value)}
              placeholder="Nama lengkap"
              className="h-12 rounded-2xl border-2 border-outline-variant bg-surface px-4 outline-none focus:border-primary"
            />
            <input
              data-testid="profile-phone"
              value={phone}
              onChange={(e) => setPhone(e.target.value)}
              placeholder="Nomor telepon (opsional)"
              className="h-12 rounded-2xl border-2 border-outline-variant bg-surface px-4 outline-none focus:border-primary"
            />
            <div className="flex gap-2">
              <Button data-testid="profile-save" disabled={updateProfile.isPending}>
                {updateProfile.isPending ? 'Menyimpan…' : 'Simpan'}
              </Button>
              <Button type="button" variant="outline" onClick={() => setEditing(false)}>
                Batal
              </Button>
            </div>
          </form>
        ) : (
          <div className="mt-2 rounded-3xl bg-surface-container p-4 text-sm">
            <p className="text-on-surface-variant">
              Nama: <span className="font-semibold text-on-surface">{user.name}</span>
            </p>
            <p className="mt-1 text-on-surface-variant">
              Telepon: <span className="font-semibold text-on-surface">{user.phone || '—'}</span>
            </p>
          </div>
        )}
      </section>

      <section>
        <h2 className="text-sm font-bold uppercase text-on-surface-variant">Ubah kata sandi</h2>
        <form onSubmit={savePassword} className="mt-2 grid gap-3 rounded-3xl bg-surface-container p-4">
          <input
            data-testid="current-password"
            required
            type="password"
            value={currentPassword}
            onChange={(e) => setCurrentPassword(e.target.value)}
            placeholder="Kata sandi saat ini"
            autoComplete="current-password"
            className="h-12 rounded-2xl border-2 border-outline-variant bg-surface px-4 outline-none focus:border-primary"
          />
          <input
            data-testid="new-password"
            required
            type="password"
            value={newPassword}
            onChange={(e) => setNewPassword(e.target.value)}
            placeholder="Kata sandi baru (min. 8)"
            autoComplete="new-password"
            minLength={8}
            className="h-12 rounded-2xl border-2 border-outline-variant bg-surface px-4 outline-none focus:border-primary"
          />
          <Button data-testid="password-save" disabled={updatePassword.isPending}>
            {updatePassword.isPending ? 'Menyimpan…' : 'Perbarui kata sandi'}
          </Button>
        </form>
      </section>

      <section>
        <div className="flex rounded-2xl bg-surface-container p-1">
          {tabs.map(([key, label, count]) => (
            <button
              key={key}
              onClick={() => setTab(key)}
              className={`h-10 flex-1 rounded-xl text-sm font-semibold transition ${tab === key ? 'bg-primary text-on-primary' : 'text-on-surface-variant'}`}
            >
              {label} ({count})
            </button>
          ))}
        </div>

        <ul className="mt-3 grid gap-2">
          {tab === 'reports' &&
            (reports.isLoading ? (
              <Spinner />
            ) : (reports.data?.data ?? []).length ? (
              reports.data!.data.map((r) => (
                <li key={r.id} className="rounded-2xl bg-surface-container p-3.5">
                  <div className="flex items-center gap-2 text-sm">
                    <span className="font-semibold">{formatPhone(phoneRel((r as any).phone_number))}</span>
                    <StatusPill status={r.status} />
                    <span className="ml-auto text-xs text-on-surface-variant">{timeAgo(r.created_at)}</span>
                  </div>
                  <p className="mt-1 line-clamp-2 text-sm text-on-surface-variant">{r.description}</p>
                </li>
              ))
            ) : (
              <EmptyState icon="campaign" title="Belum ada laporan" hint="Laporkan nomor mencurigakan dari halaman nomor." />
            ))}

          {tab === 'reviews' &&
            (reviews.isLoading ? (
              <Spinner />
            ) : (reviews.data?.data ?? []).length ? (
              reviews.data!.data.map((r) => (
                <li key={r.id} className="rounded-2xl bg-surface-container p-3.5">
                  <div className="flex items-center gap-2 text-sm">
                    <span className="font-semibold">{formatPhone(phoneRel((r as any).phone_number))}</span>
                    <StatusPill status={r.status} />
                    <span className="ml-auto text-xs text-on-surface-variant">{timeAgo(r.created_at)}</span>
                  </div>
                  <p className="mt-1 line-clamp-2 text-sm text-on-surface-variant">{r.comment ?? '—'}</p>
                </li>
              ))
            ) : (
              <EmptyState icon="rate_review" title="Belum ada ulasan" />
            ))}

          {tab === 'tags' &&
            (tags.isLoading ? (
              <Spinner />
            ) : (tags.data?.data ?? []).length ? (
              tags.data!.data.map((t) => (
                <li key={t.id} className="flex items-center gap-2 rounded-2xl bg-surface-container p-3.5 text-sm">
                  <span className="font-semibold">{t.tag?.name}</span>
                  <span className="text-on-surface-variant">untuk {formatPhone(phoneRel((t as any).phone_number))}</span>
                  <span className="ml-auto">
                    <StatusPill status={t.status} />
                  </span>
                </li>
              ))
            ) : (
              <EmptyState icon="label" title="Belum ada label" />
            ))}

          {tab === 'contributions' && <ContactContributionsPanel />}
        </ul>
      </section>
    </div>
  )
}