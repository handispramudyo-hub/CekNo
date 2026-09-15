import { NavLink, Navigate, Outlet } from 'react-router-dom'

import { Spinner } from '../../components/ui'
import { useAuth } from '../../hooks/useAuth'

const TABS = [
  ['', 'Ringkasan'],
  ['moderation', 'Moderasi'],
  ['numbers', 'Nomor'],
  ['users', 'Pengguna'],
  ['analytics', 'Analitik'],
  ['logs', 'Log'],
] as const

export function AdminLayout() {
  const { user, loading, isAdmin } = useAuth()

  if (loading) return <Spinner label="Memeriksa hak akses…" />
  if (!user) return <Navigate to="/login" replace />
  if (!isAdmin) return <Navigate to="/" replace />

  return (
    <div>
      <h1 className="text-xl font-extrabold">Panel Admin</h1>
      <div className="scrollbar-none -mx-5 mt-3 flex gap-2 overflow-x-auto px-5 pb-2">
        {TABS.map(([path, label]) => (
          <NavLink
            key={path}
            to={`/admin/${path}`}
            end={path === ''}
            className={({ isActive }) =>
              `shrink-0 rounded-full px-4 py-2 text-sm font-semibold transition ${
                isActive ? 'bg-primary text-on-primary' : 'bg-surface-container text-on-surface-variant'
              }`
            }
          >
            {label}
          </NavLink>
        ))}
      </div>
      <div className="mt-4">
        <Outlet />
      </div>
    </div>
  )
}