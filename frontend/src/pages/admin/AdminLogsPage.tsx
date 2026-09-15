import { useQuery } from '@tanstack/react-query'

import { EmptyState, Spinner } from '../../components/ui'
import { api } from '../../lib/api'
import { formatDate } from '../../lib/format'
import type { AuditLog } from '../../lib/types'

export function AdminLogsPage() {
  const q = useQuery({ queryKey: ['admin-logs'], queryFn: async () => (await api.get<{ logs: AuditLog[] }>('/admin/audit-logs')).data })
  if (q.isLoading) return <Spinner />
  const logs = q.data?.logs ?? []

  if (!logs.length) return <EmptyState icon="receipt_long" title="Belum ada log audit" />

  return (
    <ul className="grid gap-2">
      {logs.map((l) => (
        <li key={l.id} className="rounded-2xl bg-surface-container p-3.5 text-sm">
          <div className="flex items-center gap-2">
            <span className="icon text-primary">{l.action?.includes('approve') ? 'check_circle' : 'record_voice_over'}</span>
            <span className="min-w-0 flex-1">
              <b>{l.user?.name ?? 'Sistem'}</b> <span className="text-on-surface-variant">{l.action}</span>
            </span>
          </div>
          <p className="mt-1 text-xs text-on-surface-variant">
            {l.auditable_type ? `${l.auditable_type}#${l.auditable_id} · ` : ''}
            {formatDate(l.created_at)} {l.ip_address ? `· ${l.ip_address}` : ''}
          </p>
        </li>
      ))}
    </ul>
  )
}