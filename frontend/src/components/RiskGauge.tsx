import { levelMeta, scoreColor } from '../lib/format'
import type { RiskLevel } from '../lib/types'

interface Props {
  score: number | null | undefined
  level?: RiskLevel | null
  size?: number
  showLabel?: boolean
}

export function RiskGauge({ score, level, size = 168, showLabel = true }: Props) {
  const meta = levelMeta(level)
  const value = score ?? 0
  const color = level === 'low' ? meta.color : scoreColor(score)

  const radius = 64
  const circumference = Math.PI * radius
  const clamped = Math.max(4, Math.min(100, value))
  const dash = (clamped / 100) * circumference

  return (
    <div className="flex flex-col items-center gap-2" data-testid="risk-level" role="img" aria-label={`Skor risiko ${value} dari 100`}>
      <svg width={size} height={size * 0.62} viewBox="0 0 144 88" className="overflow-visible">
        <path
          d={`M 8 80 A 64 64 0 0 1 136 80`}
          fill="none"
          stroke="var(--color-surface-container-highest)"
          strokeWidth={12}
          strokeLinecap="round"
        />
        <path
          d={`M 8 80 A 64 64 0 0 1 136 80`}
          fill="none"
          stroke={color}
          strokeWidth={12}
          strokeLinecap="round"
          strokeDasharray={`${dash} ${circumference - dash}`}
          style={{ transition: 'stroke-dasharray 600ms ease' }}
        />
        <text x={72} y={62} textAnchor="middle" className="font-extrabold" fill="var(--color-on-surface)" fontSize={30}>
          {score == null ? '–' : Math.round(value)}
        </text>
        <text x={72} y={80} textAnchor="middle" className="font-medium" fill="var(--color-on-surface-variant)" fontSize={11}>
          SKOR RISIKO
        </text>
      </svg>
      {showLabel && (
        <span
          className={`inline-flex items-center gap-1 rounded-full border px-3 py-1 text-sm font-semibold ${meta.border} ${meta.bg} ${meta.text}`}
        >
          <span className="icon text-[1.1rem]">{meta.icon}</span>
          {meta.label}
        </span>
      )}
    </div>
  )
}