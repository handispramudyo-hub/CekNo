import { NavLink, Outlet } from 'react-router-dom'

import { useAuth } from '../hooks/useAuth'

function NavItem({ to, icon, label, end = false }: { to: string; icon: string; label: string; end?: boolean }) {
  return (
    <NavLink
      to={to}
      end={end}
      className={({ isActive }) =>
        `flex min-w-0 flex-1 flex-col items-center gap-0.5 py-2 text-[11px] font-medium transition ${
          isActive ? 'text-primary' : 'text-on-surface-variant'
        }`
      }
    >
      {() => (
        <>
          <span className="icon filled">{icon}</span>
          <span className="truncate">{label}</span>
        </>
      )}
    </NavLink>
  )
}

export function AppLayout() {
  const { user, isAdmin } = useAuth()

  return (
    <div className="mx-auto flex min-h-dvh w-full max-w-md flex-col bg-surface">
      <header className="sticky top-0 z-20 flex items-center gap-2 px-5 py-4 backdrop-blur">
        <span className="icon text-2xl text-primary">shield</span>
        <span className="text-lg font-extrabold tracking-tight text-primary-dark">CekNO</span>
        {user && (
          <span className="ml-auto rounded-full bg-secondary-container px-3 py-1 text-xs font-semibold text-on-secondary-container">
            {user.name}
          </span>
        )}
      </header>

      <main className="flex-1 px-5 pb-24">
        <Outlet />
      </main>

      <nav className="fixed inset-x-0 bottom-0 z-20 mx-auto flex w-full max-w-md items-center justify-around border-t border-outline-variant/60 bg-surface-container px-2 safe-bottom">
        <NavItem to="/" icon="home" label="Beranda" end />
        <NavItem to="/report" icon="add_alert" label="Lapor" />
        {isAdmin && <NavItem to="/admin" icon="admin_panel_settings" label="Admin" />}
        <NavItem to="/history" icon="history" label="Riwayat" />
        <NavItem to={user ? '/profile' : '/login'} icon="person" label={user ? 'Profil' : 'Masuk'} />
      </nav>
    </div>
  )
}