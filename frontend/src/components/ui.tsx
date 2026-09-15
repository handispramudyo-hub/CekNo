import type { ReactNode } from 'react'
import { Link } from 'react-router-dom'

export function Button({
  children,
  variant = 'primary',
  className = '',
  ...props
}: React.ButtonHTMLAttributes<HTMLButtonElement> & {
  variant?: 'primary' | 'secondary' | 'outline' | 'danger' | 'ghost'
}) {
  const styles: Record<string, string> = {
    primary: 'bg-primary text-on-primary hover:bg-primary-dark disabled:bg-outline-variant disabled:text-on-surface-variant',
    secondary: 'bg-secondary-container text-on-secondary-container',
    outline: 'border border-outline text-primary',
    danger: 'bg-error-container text-on-error-container',
    ghost: 'text-primary',
  }
  return (
    <button
      type={props.type ?? 'submit'}
      className={`inline-flex h-11 items-center justify-center gap-2 rounded-2xl px-5 text-sm font-semibold transition active:scale-[.98] disabled:cursor-not-allowed ${styles[variant]} ${className}`}
      {...props}
    >
      {children}
    </button>
  )
}

export function IconButton({ icon, label, className = '', ...props }: React.ButtonHTMLAttributes<HTMLButtonElement> & {
  icon: string
  label: string
}) {
  return (
    <button
      aria-label={label}
      className={`inline-flex h-11 w-11 items-center justify-center rounded-2xl text-primary transition active:bg-surface-container ${className}`}
      type="button"
      {...props}
    >
      <span className="icon">{icon}</span>
    </button>
  )
}

export function StatCard({ icon, label, value }: { icon: string; label: string; value: string | number }) {
  return (
    <div className="flex items-center gap-3 rounded-3xl bg-surface-container p-4">
      <span className="icon flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-primary-container text-on-primary-container">
        {icon}
      </span>
      <div className="min-w-0">
        <p className="text-sm text-on-surface-variant">{label}</p>
        <p className="truncate text-xl font-bold">{value}</p>
      </div>
    </div>
  )
}

export function Card({ children, className = '' }: { children: ReactNode; className?: string }) {
  return <div className={`rounded-3xl bg-surface-container p-4 ${className}`}>{children}</div>
}

export function EmptyState({ icon = 'search_off', title, hint }: { icon?: string; title: string; hint?: string }) {
  return (
    <div className="flex flex-col items-center gap-2 px-8 py-10 text-center">
      <span className="icon text-4xl text-outline">{icon}</span>
      <p className="font-semibold">{title}</p>
      {hint && <p className="text-sm text-on-surface-variant">{hint}</p>}
    </div>
  )
}

export function Spinner({ label = 'Memuat…' }: { label?: string }) {
  return (
    <div className="flex flex-col items-center gap-3 py-14" role="status">
      <span className="h-9 w-9 animate-spin rounded-full border-4 border-primary-container border-t-primary" />
      <p className="text-sm text-on-surface-variant">{label}</p>
    </div>
  )
}

export function NotFound() {
  return (
    <div className="px-6 py-16 text-center">
      <span className="icon text-5xl text-outline">folder_off</span>
      <p className="mt-3 font-bold">Halaman tidak ditemukan</p>
      <Link to="/" className="mt-4 inline-block font-semibold text-primary underline">
        Kembali ke Beranda
      </Link>
    </div>
  )
}