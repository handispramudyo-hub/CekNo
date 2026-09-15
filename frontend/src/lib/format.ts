import type { RiskLevel } from './types'

export interface LevelMeta {
  label: string
  short: string
  color: string
  bg: string
  text: string
  border: string
  icon: string
  caption: string
}

const META: Record<RiskLevel, LevelMeta> = {
  low: {
    label: 'Risiko Rendah',
    short: 'Rendah',
    color: '#2f6b2f',
    bg: '#d5f2d5',
    text: 'text-[#2f6b2f]',
    border: 'border-[#2f6b2f]/30',
    icon: 'verified_user',
    caption: 'Belum ada laporan mencurigakan berarti.',
  },
  caution: {
    label: 'Perlu Diwaspadai',
    short: 'Waspada',
    color: '#8a6d00',
    bg: '#f8e7a0',
    text: 'text-[#8a6d00]',
    border: 'border-[#8a6d00]/30',
    icon: 'error_outline',
    caption: 'Ada beberapa laporan; tetap hati-hati.',
  },
  risky: {
    label: 'Risiko Sedang',
    short: 'Sedang',
    color: '#c2410c',
    bg: '#ffe0c2',
    text: 'text-[#c2410c]',
    border: 'border-[#c2410c]/30',
    icon: 'warning',
    caption: 'Beberapa laporan penipuan terverifikasi.',
  },
  high: {
    label: 'Risiko Tinggi',
    short: 'Tinggi',
    color: '#ba1a1a',
    bg: '#ffdad6',
    text: 'text-[#ba1a1a]',
    border: 'border-[#ba1a1a]/30',
    icon: 'gpp_bad',
    caption: 'Banyak laporan penipuan & analisis AI mendukung.',
  },
}

export function levelMeta(level?: RiskLevel | null): LevelMeta {
  return META[level ?? 'low']
}

export function scoreColor(score: number | null | undefined): string {
  if (score == null) return 'text-on-surface-variant'
  if (score >= 75) return '#ba1a1a'
  if (score >= 50) return '#c2410c'
  if (score >= 25) return '#8a6d00'
  return '#2f6b2f'
}

export function formatPhone(phone: string): string {
  const digits = phone.replace(/[^0-9]/g, '')
  if (digits.startsWith('62')) {
    const local = digits.replace(/^620?/, '0')
    const groups = local.match(/^(0\d{2})(\d{3,4})(\d{2,4})$/)
    return groups ? `${groups[1]} ${groups[2]} ${groups[3]}` : local
  }
  return phone
}

/** Ambil nomor dari relasi Eloquent `phone_number` ({ phone_number: '…' }) atau string apa adanya. */
export function phoneRel(rel: unknown): string {
  if (typeof rel === 'string') return rel
  const r = rel as { phone_number?: string } | null | undefined
  return r?.phone_number ?? ''
}

export function formatDate(value?: string | null): string {
  if (!value) return '-'
  const d = new Date(value)
  if (Number.isNaN(d.getTime())) return value
  return new Intl.DateTimeFormat('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  }).format(d)
}

export function timeAgo(value?: string | null): string {
  if (!value) return ''
  const d = new Date(value).getTime()
  const diff = Date.now() - d
  const min = Math.floor(diff / 60000)
  if (min < 1) return 'baru saja'
  if (min < 60) return `${min} mnt lalu`
  const hr = Math.floor(min / 60)
  if (hr < 24) return `${hr} jam lalu`
  const day = Math.floor(hr / 24)
  if (day < 7) return `${day} hari lalu`
  return formatDate(value)
}

export async function copyText(text: string): Promise<boolean> {
  try {
    await navigator.clipboard.writeText(text)
    return true
  } catch {
    return false
  }
}