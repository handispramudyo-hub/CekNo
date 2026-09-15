import { useQuery } from '@tanstack/react-query'

import { Spinner, StatCard } from '../../components/ui'
import { api } from '../../lib/api'
import { timeAgo } from '../../lib/format'
import type { DashboardData } from '../../lib/types'

export function AdminDashboardPage() {
  const q = useQuery({ queryKey: ['admin-dashboard'], queryFn: async () => (await api.get<DashboardData>('/admin/dashboard')).data })

  if (q.isLoading) return <Spinner label="Memuat panel…" />

  const d = q.data
  if (!d) return <p className="pt-10 text-center text-sm">Gagal memuat dashboard.</p>

  return (
    <div className="flex flex-col gap-4">
      <h1 className="text-xl font-extrabold">Ringkasan</h1>
      <div className="grid grid-cols-2 gap-3">
        <StatCard icon="flag" label="Laporan dipending" value={d.pending_reports} />
        <StatCard icon="rate_review" label="Ulasan dipending" value={d.pending_reviews} />
        <StatCard icon="label" label="Label dipending" value={d.pending_tags} />
        <StatCard icon="person_add" label="Pengguna baru (7 hari)" value={d.new_users} />
      </div>
      <StatCard icon="call_made" label="Total nomor terindeks" value={d.total_numbers} />

      <section>
        <h2 className="mb-2 font-bold">Aktivitas moderasi terakhir</h2>
        {(d.recent_moderation ?? []).length === 0 ? (
          <p className="text-sm text-on-surface-variant">Belum ada aktivitas.</p>
        ) : (
          <ul className="grid gap-2">
            {d.recent_moderation.map((m) => (
              <li key={m.id} className="flex items-center gap-3 rounded-2xl bg-surface-container p-3 text-sm">
                <span className="icon text-primary">{m.action === 'approved' ? 'check_circle' : 'cancel'}</span>
                <span className="min-w-0 flex-1 truncate">
                  <b>{m.admin?.name ?? 'Admin'}</b>{' '}
                  <span className="text-on-surface-variant">
                    {m.action} {m.target_type}
                  </span>
                </span>
                <span className="text-xs text-on-surface-variant">{timeAgo(String(m.created_at ?? ''))}</span>
              </li>
            ))}
          </ul>
        )}
      </section>
    </div>
  )
}