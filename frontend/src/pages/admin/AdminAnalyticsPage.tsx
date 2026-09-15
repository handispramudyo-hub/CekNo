import { useQuery } from '@tanstack/react-query'
import { Cell, Pie, PieChart, ResponsiveContainer, Tooltip } from 'recharts'

import { Card, Spinner } from '../../components/ui'
import { api } from '../../lib/api'
import { levelMeta } from '../../lib/format'

const LEVEL_ORDER = ['low', 'caution', 'risky', 'high'] as const

interface Analytics {
  risk_distribution: Record<string, number>
  reports_last_7_days: number
  reports_by_category: Record<string, number>
}

export function AdminAnalyticsPage() {
  const q = useQuery({ queryKey: ['admin-analytics'], queryFn: async () => (await api.get<Analytics>('/admin/analytics')).data })

  if (q.isLoading) return <Spinner />
  const d = q.data

  const riskData = LEVEL_ORDER.filter((l) => (d?.risk_distribution?.[l] ?? 0) > 0).map((l) => ({
    name: levelMeta(l).short,
    value: d?.risk_distribution?.[l] ?? 0,
    color: levelMeta(l).color,
  }))
  const categoryData = Object.entries(d?.reports_by_category ?? {}).map(([name, value]) => ({ name, value }))

  return (
    <div className="grid gap-4">
      <Card className="p-0">
        <h2 className="p-4 pb-0 font-bold">Distribusi tingkat risiko</h2>
        {riskData.length ? (
          <ResponsiveContainer width="100%" height={220}>
            <PieChart>
              <Pie data={riskData} dataKey="value" nameKey="name" innerRadius={55} outerRadius={85} paddingAngle={3}>
                {riskData.map((entry) => (
                  <Cell key={entry.name} fill={entry.color} />
                ))}
              </Pie>
              <Tooltip />
            </PieChart>
          </ResponsiveContainer>
        ) : (
          <p className="p-8 text-center text-sm text-on-surface-variant">Belum ada data.</p>
        )}
      </Card>

      <Card>
        <h2 className="mb-3 font-bold">Laporan disetujui per kategori</h2>
        {categoryData.length ? (
          <div className="grid gap-2">
            {categoryData.map((c) => {
              const max = Math.max(...categoryData.map((x) => x.value))
              return (
                <div key={c.name} className="flex items-center gap-3 text-sm">
                  <span className="w-28 truncate capitalize">{c.name}</span>
                  <div className="h-3 flex-1 overflow-hidden rounded-full bg-surface-container-highest">
                    <div className="h-full rounded-full bg-tertiary" style={{ width: `${(c.value / max) * 100}%` }} />
                  </div>
                  <span className="w-8 text-right font-semibold">{c.value}</span>
                </div>
              )
            })}
          </div>
        ) : (
          <p className="text-sm text-on-surface-variant">Belum ada data.</p>
        )}
      </Card>

      <Card className="text-center">
        <p className="text-4xl font-extrabold text-primary">{d?.reports_last_7_days ?? 0}</p>
        <p className="mt-1 text-sm text-on-surface-variant">laporan masuk 7 hari terakhir</p>
      </Card>
    </div>
  )
}