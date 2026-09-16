import { useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import toast from 'react-hot-toast'

import { Button, EmptyState, Spinner } from '../../components/ui'
import { api, apiError } from '../../lib/api'
import { formatDate, phoneRel } from '../../lib/format'

interface Page<T> {
  data: T[]
}

type Kind = 'reports' | 'reviews' | 'tags' | 'contributions'

const KIND_META: Record<Kind, { label: string; icon: string }> = {
  reports: { label: 'Laporan', icon: 'flag' },
  reviews: { label: 'Ulasan', icon: 'rate_review' },
  tags: { label: 'Label', icon: 'label' },
  contributions: { label: 'Kontribusi', icon: 'contacts' },
}

function ModerateButtons({ onModerate, disabled }: { onModerate: (action: 'approved' | 'rejected') => void; disabled?: boolean }) {
  return (
    <div className="mt-2 flex gap-2">
      <Button className="h-9 flex-1 px-2 text-xs" variant="secondary" onClick={() => onModerate('approved')} disabled={disabled}>
        <span className="icon">check</span> Setujui
      </Button>
      <Button className="h-9 flex-1 px-2 text-xs" variant="danger" onClick={() => onModerate('rejected')} disabled={disabled}>
        <span className="icon">close</span> Tolak
      </Button>
    </div>
  )
}

export function AdminModerationPage() {
  const [kind, setKind] = useState<Kind>('reports')
  const qc = useQueryClient()
  const meta = KIND_META[kind]

  const q = useQuery({
    queryKey: ['admin', kind],
    queryFn: async () => (await api.get<Page<any>>(`/admin/${kind}`)).data,
  })

  const moderate = useMutation({
    mutationFn: async ({ id, action }: { id: number; action: 'approved' | 'rejected' }) => {
      const reason = action === 'rejected'
        ? window.prompt('Alasan penolakan (dibutuhkan untuk transparansi):')
        : null
      if (action === 'rejected' && !reason) return
      await api.post(`/admin/${kind}/${id}/moderate`, { action, reason })
    },
    onSuccess: () => {
      toast.success('Moderasi selesai')
      qc.invalidateQueries({ queryKey: ['admin', kind] })
      qc.invalidateQueries({ queryKey: ['admin-dashboard'] })
    },
    onError: (err) => toast.error(apiError(err)),
  })

  const items: any[] = q.data?.data ?? []

  return (
    <div>
      <div className="flex gap-2">
        {(Object.keys(KIND_META) as Kind[]).map((k) => (
          <button
            key={k}
            onClick={() => setKind(k)}
            className={`h-10 flex-1 rounded-xl text-sm font-semibold transition ${kind === k ? 'bg-primary text-on-primary' : 'bg-surface-container text-on-surface-variant'}`}
          >
            {KIND_META[k].label}
          </button>
        ))}
      </div>

      <div className="mt-3">
        {q.isLoading ? (
          <Spinner />
        ) : items.length === 0 ? (
          <EmptyState icon="inbox" title={`Tidak ada ${meta.label.toLocaleLowerCase()} pending`} hint="Kerja bagus!" />
        ) : (
          <ul className="grid gap-2">
            {items.map((item: any) => {
              const number = phoneRel(item.phone_number)
              const user = item.user?.name ?? item.tag?.name ?? 'Pengguna'
              const body = item.label ?? (item.description ?? (item.comment ?? `Label: ${item.tag?.name}`))
              return (
                <li key={item.id} className="rounded-2xl bg-surface-container p-3.5">
                  <div className="flex items-center gap-2 text-sm">
                    <span className="rounded-lg bg-primary-container px-2 py-0.5 font-mono text-xs text-on-primary-container">{number || `#${item.phone_number_id}`}</span>
                    <span className="truncate font-semibold">{user}</span>
                    <span className="ml-auto text-[11px] text-on-surface-variant">{formatDate(item.created_at)}</span>
                  </div>
                  <p className="mt-2 line-clamp-3 text-sm leading-relaxed">{body ?? `Label: ${item.tag?.name}`}</p>
                  <ModerateButtons disabled={moderate.isPending} onModerate={(action) => moderate.mutate({ id: item.id, action })} />
                </li>
              )
            })}
          </ul>
        )}
      </div>
    </div>
  )
}