import { useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import { useNavigate, useParams } from 'react-router-dom'
import toast from 'react-hot-toast'

import { Button, Card, EmptyState, Spinner } from '../components/ui'
import { RiskGauge } from '../components/RiskGauge'
import { api, apiError, tokenStore } from '../lib/api'
import { copyText, formatDate, formatPhone, levelMeta, timeAgo } from '../lib/format'
import type { NumberDetail, Report, Review, TagChip } from '../lib/types'

function FactorBar({ label, value }: { label: string; value: number | null | undefined }) {
  const pct = Math.max(0, Math.min(100, value ?? 0))
  return (
    <div>
      <div className="mb-1 flex items-center justify-between text-xs">
        <span className="font-medium text-on-surface-variant">{label}</span>
        <span className="font-bold">{pct}</span>
      </div>
      <div className="h-2 overflow-hidden rounded-full bg-surface-container-highest">
        <div className="h-full rounded-full bg-primary transition-all" style={{ width: `${pct}%` }} />
      </div>
    </div>
  )
}

function ReportRow({ report }: { report: Report }) {
  const meta = levelMeta()
  return (
    <li className="rounded-2xl bg-surface padding-0">
      <div className="rounded-2xl bg-surface p-3.5">
        <div className="flex items-center gap-2 text-xs text-on-surface-variant">
          <span className={`icon text-base ${meta.text}`}>report</span>
          <span className="font-semibold">{report.category?.name ?? 'Laporan komunitas'}</span>
          <span className="ml-auto">{timeAgo(report.created_at)}</span>
        </div>
        {report.description && <p className="mt-1.5 text-sm leading-relaxed">{report.description}</p>}
      </div>
    </li>
  )
}

function ReviewRow({ review }: { review: Review }) {
  return (
    <li className="rounded-2xl bg-surface p-3.5">
      <div className="flex items-center justify-between">
        <div className="flex gap-0.5" role="img" aria-label={`Rating ${review.rating} dari 5`}>
          {[1, 2, 3, 4, 5].map((i) => (
            <span key={i} className={`icon text-[1.05rem] ${i <= review.rating ? 'text-tertiary' : 'text-outline-variant'}`}>
              star
            </span>
          ))}
        </div>
        <span className="text-xs text-on-surface-variant">{timeAgo(review.created_at)}</span>
      </div>
      {review.comment && <p className="mt-1.5 text-sm leading-relaxed">{review.comment}</p>}
    </li>
  )
}

export function NumberPage() {
  const { number: rawNumber } = useParams()
  const numberParam = (rawNumber ?? '').replace(/\D/g, '')
  const navigate = useNavigate()
  const [reviewOpen, setReviewOpen] = useState(false)
  const [rating, setRating] = useState(5)
  const [comment, setComment] = useState('')
  const [sending, setSending] = useState(false)
  const [proposedTag, setProposedTag] = useState('')

  const search = useQuery({
    queryKey: ['search', numberParam],
    queryFn: async () =>
      (await api.get<{ phone_number: { id: number }; risk_assessment: import('../lib/types').RiskAssessment | null }>(
        '/numbers/search',
        { params: { phone: numberParam } },
      )).data,
    retry: 1,
    enabled: numberParam.length > 0,
  })

  const detail = useQuery({
    queryKey: ['number', numberParam],
    queryFn: async () => (await api.get<NumberDetail>(`/numbers/${numberParam}`)).data,
    enabled: numberParam.length > 0,
  })

  if (search.isLoading) return <Spinner label="Menghafal nomor…" />
  if (search.isError) {
    return (
      <div className="pt-10">
        <EmptyState icon="block" title="Nomor tidak dapat diproses" hint={apiError(search.error)} />
        <Button onClick={() => navigate('/')} className="mx-auto mt-2">
          Coba lagi
        </Button>
      </div>
    )
  }
  const number = detail.data
  const risk = search.data?.risk_assessment ?? number?.risk_assessment
  const meta = levelMeta(risk?.risk_level)

  async function submitReview() {
    if (!provide('Masuk untuk menulis ulasan')) return
    setSending(true)
    try {
      await api.post(`/numbers/${numberParam}/reviews`, { rating, comment })
      toast.success('Ulasan terkirim dan menunggu moderasi')
      setReviewOpen(false)
      setComment('')
      detail.refetch()
    } catch (err) {
      toast.error(apiError(err))
    } finally {
      setSending(false)
    }
  }

  async function proposeTag() {
    if (!provide('Masuk untuk menambah tag')) return
    const name = proposedTag.trim()
    if (!name) return
    try {
      await api.post(`/numbers/${numberParam}/tags`, { name })
      toast.success('Tag diusulkan, menunggu persetujuan')
      setProposedTag('')
    } catch (err) {
      toast.error(apiError(err))
    }
  }

  function provide(message: string): boolean {
    if (tokenStore.get()) return true
    toast(message)
    setTimeout(() => navigate('/login'), 700)
    return false
  }

  return (
    <div className="flex flex-col gap-4">
      <section className="text-center">
        <div className="mt-2 flex items-center justify-center gap-2">
          <h1 className="select-all text-xl font-extrabold tracking-tight">{formatPhone(numberParam)}</h1>
          <button type="button" className="icon text-primary" aria-label="Salin nomor" onClick={() => copyText(numberParam).then((ok) => ok && toast.success('Nomor disalin'))}>
            content_copy
          </button>
        </div>
        <p className="mt-1 text-xs text-on-surface-variant">
          {number ? `${number.search_count ?? 0}x dicari` : ''}
          {number?.status === 'hidden' ? ' • Nomor sedang disembunyikan' : ''}
        </p>
      </section>

      {risk ? (
        <section className="flex flex-col items-center gap-3 rounded-3xl bg-surface-container p-5">
          <RiskGauge score={risk.final_score ?? 0} level={risk.risk_level} />
          <p className="max-w-xs text-center text-sm leading-relaxed">{risk.explanation ?? meta.caption}</p>
          <div className="mt-1 w-full space-y-3">
            <FactorBar label="Skor komunitas" value={risk.community_score ?? 0} />
            <FactorBar label="Skor aturan (rule engine)" value={risk.rule_score ?? 0} />
            {(risk.xgboost_probability ?? null) !== null && (
              <FactorBar label="Prediksi XGBoost" value={(risk.xgboost_probability ?? 0) * 100} />
            )}
            {(risk.indobert_probability ?? null) !== null && (
              <FactorBar label="Analisis IndoBERT" value={(risk.indobert_probability ?? 0) * 100} />
            )}
          </div>
        </section>
      ) : (
        <section className="rounded-3xl bg-surface-container p-5 text-center text-sm text-on-surface-variant">
          Belum ada penilaian; laporan pertama akan memicu analisis risiko.
        </section>
      )}

      <section className="grid grid-cols-2 gap-3">
        <Button
          onClick={() => {
            if (!provide('Masuk untuk melaporkan nomor ini')) return
            navigate(`/report?phone=${numberParam}`)
          }}
        >
          <span className="icon">add_alert</span> Lapor nomor ini
        </Button>
        <Button variant="secondary" onClick={() => setReviewOpen((v) => !v)}>
          <span className="icon">rate_review</span> Beri ulasan
        </Button>
      </section>

      {reviewOpen && (
        <Card>
          <div className="flex justify-between">
            <h3 className="font-bold">Tulis ulasan</h3>
            <div className="flex gap-0.5">
              {[1, 2, 3, 4, 5].map((i) => (
                <button key={i} type="button" onClick={() => setRating(i)} aria-label={`Beri ${i} bintang`}>
                  <span className={`icon ${i <= rating ? 'text-tertiary' : 'text-outline-variant'}`}>star</span>
                </button>
              ))}
            </div>
          </div>
          <textarea
            value={comment}
            onChange={(e) => setComment(e.target.value)}
            rows={3}
            placeholder="Bagaimana pengalaman Anda dengan nomor ini?"
            className="mt-2 w-full rounded-2xl border-2 border-outline-variant bg-surface p-3 text-sm outline-none focus:border-primary"
          />
          <Button className="mt-3 w-full" onClick={submitReview} disabled={sending}>
            Kirim ulasan
          </Button>
        </Card>
      )}

      <section>
        <h2 className="mb-2 flex items-center gap-2 font-bold">
          <span className="icon text-primary">label</span> Label komunitas
        </h2>
        {number?.tags?.length ? (
          <div className="flex flex-wrap gap-2">
            {number.tags.map((t: TagChip) => (
              <span key={t.id} className="rounded-full border border-outline-variant bg-surface-container px-3 py-1.5 text-sm">
                {t.tag.name}
                {t.must_approve && <span className="ml-1 text-[10px] text-on-surface-variant">(terverifikasi)</span>}
              </span>
            ))}
          </div>
        ) : (
          <p className="text-sm text-on-surface-variant">Belum ada label.</p>
        )}
        <div className="mt-3 flex gap-2">
          <input
            value={proposedTag}
            onChange={(e) => setProposedTag(e.target.value)}
            placeholder="Usulkan label (mis. bank, pinjol)"
            className="h-10 min-w-0 flex-1 rounded-xl border-2 border-outline-variant bg-surface px-3 text-sm outline-none focus:border-primary"
          />
          <Button className="h-10 px-4" onClick={proposeTag} disabled={!proposedTag.trim()}>
            Tambah
          </Button>
        </div>
      </section>

      <section>
        <h2 className="mb-2 flex items-center gap-2 font-bold">
          <span className="icon text-primary">campaign</span> Laporan komunitas
          {number?.reports?.length ? <span className="text-sm font-normal text-on-surface-variant">({number.reports.length})</span> : null}
        </h2>
        {number?.reports?.length ? (
          <ul className="grid gap-2">
            {number.reports.map((r) => (
              <ReportRow key={r.id} report={r} />
            ))}
          </ul>
        ) : (
          <p className="text-sm text-on-surface-variant">Belum ada laporan disetujui.</p>
        )}
      </section>

      <section>
        <h2 className="mb-2 flex items-center gap-2 font-bold">
          <span className="icon text-primary">reviews</span> Ulasan
          {number?.reviews?.length ? <span className="text-sm font-normal text-on-surface-variant">({number.reviews.length})</span> : null}
        </h2>
        {number?.reviews?.length ? (
          <ul className="grid gap-2">
            {number.reviews.map((r) => (
              <ReviewRow key={r.id} review={r} />
            ))}
          </ul>
        ) : (
          <p className="text-sm text-on-surface-variant">Belum ada ulasan disetujui.</p>
        )}
      </section>

      {risk?.assessed_at && (
        <p className="text-center text-xs text-on-surface-variant">Penilaian terakhir: {formatDate(risk.assessed_at)}</p>
      )}
    </div>
  )
}