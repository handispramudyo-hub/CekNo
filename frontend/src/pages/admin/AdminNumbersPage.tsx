import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import toast from 'react-hot-toast'

import { Button, EmptyState, Spinner, StatCard } from '../../components/ui'
import { api, apiError } from '../../lib/api'
import { formatPhone, levelMeta } from '../../lib/format'
import type { PhoneNumber } from '../../lib/types'

interface Page<T> {
  data: T[]
  total?: number
}

export function AdminNumbersPage() {
  const qc = useQueryClient()
  const q = useQuery({ queryKey: ['admin-numbers'], queryFn: async () => (await api.get<Page<PhoneNumber>>('/admin/numbers')).data })

  const setStatus = useMutation({
    mutationFn: async ({ id, status }: { id: number; status: 'active' | 'hidden' }) =>
      api.patch(`/admin/numbers/${id}/status`, { status }),
    onSuccess: () => {
      toast.success('Status nomor diperbarui')
      qc.invalidateQueries({ queryKey: ['admin-numbers'] })
    },
    onError: (err) => toast.error(apiError(err)),
  })

  if (q.isLoading) return <Spinner />
  const items: PhoneNumber[] = q.data?.data ?? []

  return (
    <div>
      <StatCard icon="call_made" label="Total nomor" value={q.data?.total ?? items.length} />
      {items.length === 0 ? (
        <EmptyState icon="call_made" title="Belum ada nomor terindeks" />
      ) : (
        <ul className="mt-3 grid gap-2">
          {items.map((n) => {
            const meta = levelMeta(n.risk_level)
            return (
              <li key={n.id} className="flex items-center gap-3 rounded-2xl bg-surface-container p-3.5 text-sm">
                <span className="min-w-0 flex-1">
                  <p className="truncate font-mono font-semibold">{formatPhone(n.phone_number)}</p>
                  <p className="text-xs text-on-surface-variant">{n.search_count}x dicari · {n.status}</p>
                </span>
                <span className={`rounded-full px-2.5 py-1 text-xs font-semibold ${meta.bg} ${meta.text}`}>{meta.short}</span>
                <Button
                  className="h-9 px-3 text-xs"
                  variant={n.status === 'hidden' ? 'secondary' : 'outline'}
                  onClick={() => setStatus.mutate({ id: n.id, status: n.status === 'hidden' ? 'active' : 'hidden' })}
                  disabled={setStatus.isPending}
                >
                  {n.status === 'hidden' ? 'Tampilkan' : 'Sembunyikan'}
                </Button>
              </li>
            )
          })}
        </ul>
      )}
    </div>
  )
}