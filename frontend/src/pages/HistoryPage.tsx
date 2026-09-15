import { useQuery } from '@tanstack/react-query'
import { Link, Navigate } from 'react-router-dom'

import { EmptyState, Spinner } from '../components/ui'
import { useAuth } from '../hooks/useAuth'
import { api } from '../lib/api'
import { formatPhone, phoneRel, timeAgo } from '../lib/format'
import type { SearchHistoryItem } from '../lib/types'

export function HistoryPage() {
  const { user, loading } = useAuth()
  const q = useQuery({
    queryKey: ['history'],
    queryFn: async () => (await api.get<{ history: (SearchHistoryItem & { phone_number?: unknown; searched_at?: string })[] }>('/user/history')).data,
    enabled: !!user,
  })

  if (!user) {
    if (!loading) return <Navigate to="/login?next=/history" replace />
    return <Spinner />
  }
  if (q.isLoading) return <Spinner />
  const items = q.data?.history ?? []

  return (
    <div>
      <h1 className="text-xl font-extrabold">Riwayat pencarian</h1>
      <p className="mt-1 text-sm text-on-surface-variant">Nomor yang pernah Anda cek.</p>
      {items.length === 0 ? (
        <EmptyState icon="history" title="Belum ada riwayat" hint="Coba cek nomor dari beranda." />
      ) : (
        <ul className="mt-4 grid gap-2">
          {items.map((h) => (
            <li key={h.id}>
              <Link
                to={`/numbers/${(h as any).phone_number.replace(/\D/g, '')}`}
                className="flex items-center gap-3 rounded-2xl bg-surface-container p-3.5 transition active:bg-surface-container-high"
              >
                <span className="icon rounded-xl bg-primary-container p-2 text-primary">call_received</span>
                <span className="font-semibold">{formatPhone(phoneRel((h as any).phone_number))}</span>
                <span className="ml-auto text-xs text-on-surface-variant">{timeAgo(h.searched_at ?? h.created_at)}</span>
              </Link>
            </li>
          ))}
        </ul>
      )}
    </div>
  )
}