import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import toast from 'react-hot-toast'

import { Button, EmptyState, Spinner } from '../../components/ui'
import { api, apiError } from '../../lib/api'
import type { User } from '../../lib/types'

interface Page<T> {
  data: T[]
}

export function AdminUsersPage() {
  const qc = useQueryClient()
  const q = useQuery({ queryKey: ['admin-users'], queryFn: async () => (await api.get<Page<User>>('/admin/users')).data })

  const change = useMutation({
    mutationFn: async ({ id, patch }: { id: number; patch: { status?: 'active' | 'suspended'; role?: 'user' | 'admin' } }) =>
      patch.role ? api.patch(`/admin/users/${id}/role`, patch) : api.patch(`/admin/users/${id}/status`, patch),
    onSuccess: () => {
      toast.success('Pengguna diperbarui')
      qc.invalidateQueries({ queryKey: ['admin-users'] })
    },
    onError: (err) => toast.error(apiError(err)),
  })

  if (q.isLoading) return <Spinner />
  const users: User[] = q.data?.data ?? []

  if (users.length === 0) return <EmptyState icon="people" title="Belum ada pengguna" />

  return (
    <ul className="grid gap-2">
      {users.map((u) => (
        <li key={u.id} className="rounded-2xl bg-surface-container p-3.5">
          <div className="flex items-center gap-3">
            <span className="icon flex h-10 w-10 items-center justify-center rounded-xl bg-primary-container text-xl text-on-primary-container">
              person
            </span>
            <div className="min-w-0 flex-1">
              <p className="truncate font-semibold">
                {u.name} <span className="text-xs font-medium text-on-surface-variant">({u.role})</span>
              </p>
              <p className="truncate text-xs text-on-surface-variant">{u.email}</p>
            </div>
            <span className={`h-2.5 w-2.5 rounded-full ${u.status === 'suspended' ? 'bg-error' : 'bg-[#2f6b2f]'}`} />
          </div>
          <div className="mt-2 flex gap-2">
            <Button
              className="h-9 flex-1 px-2 text-xs"
              variant={u.role === 'admin' ? 'secondary' : 'outline'}
              onClick={() => change.mutate({ id: u.id, patch: { role: u.role === 'admin' ? 'user' : 'admin' } })}
              disabled={change.isPending}
            >
              {u.role === 'admin' ? 'Cabut admin' : 'Jadikan admin'}
            </Button>
            <Button
              className="h-9 flex-1 px-2 text-xs"
              variant="danger"
              onClick={() => change.mutate({ id: u.id, patch: { status: u.status === 'suspended' ? 'active' : 'suspended' } })}
              disabled={change.isPending}
            >
              {u.status === 'suspended' ? 'Aktifkan' : 'Tangguhkan'}
            </Button>
          </div>
        </li>
      ))}
    </ul>
  )
}