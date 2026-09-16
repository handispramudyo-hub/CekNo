# Rencana Pengembangan

Rencana bertahap pengembangan CekNO (NUMTAG). Mesin inti (backend, web, ML, Docker, CI) sudah ada; fase di bawah fokus **menambah logika/fitur** tanpa rebranding. Setiap fase berakhir dengan verifikasi (test hijau).

## Status Ringkas

| Fase | Isi | Status |
|---|---|---|
| 1 | Dokumentasi & baseline | **Berjalan** |
| 2 | Search parity + cache Redis | Belum |
| 3 | Contact contributions & consent | Belum |
| 4 | Moderasi & anti-abuse lanjutan | Belum |
| 5 | ML dataset & endpoint internal | Belum |
| 6 | Risk engine configurable | Belum |
| 7 | Redis, queue, security, testing | Belum |
| 8 | Docker/production + CI Flutter | Belum |
| Mobile | Flutter app (jalur paralel) | Belum |

## Fase 1 — Dokumentasi & Baseline
- `docs/architecture.md`, `docs/database-schema.md`, `docs/development-plan.md`, `docs/privacy-policy.md`, `docs/terms.md`.
- Regression: backend `php artisan test` (sqlite :memory:), frontend `typecheck/lint/test/build`, `pytest` ml-service, E2E Playwright vs stack Docker.
- Kriteria: semua hijau tanpa perubahan perilaku.

## Fase 2 — Search Parity + Cache
- Route web `/search` & `/number/:number` (alias dari `/numbers/:phone`).
- Redis cache hasil pencarian nomor populer (`cekno:number:<normalized>`, TTL) → hit sangat cepat, cache miss → query + tulis cache.
- Invalidate cache saat moderasi/agregasi mengubah skor.
- Verifikasi: `curl` + E2E search.

## Fase 3 — Contact Contributions & Consent (inti baru)
- Migrasi: `contact_contributions`, `consents`; alter `phone_numbers` (+ `total_contributions`, `contributor_count`).
- Model + relasi + factory + seeder.
- API:
  - `POST /api/contact-contributions` (bulk, batch dari mobile).
  - `POST /api/contact-contributions/sync` (upsert massal, dedupe `UNIQUE(user_id, phone_number_id, label_normalized)`).
  - `DELETE /api/contact-contributions/{id}` (withdraw → status `withdrawn`).
- Consent: `consents` dicatat saat user terima policy; setiap kontribusi membawa `consent_version`.
- Anti-abuse: limit frekuensi kontribusi per user, deteksi duplikat, hitung `contributor_count` unik.
- Agregasi nametag: "label — N kontribusi komunitas" (approved only).
- Scheduler (Laravel Scheduler, cron di container backend): `contributions:aggregate`, `numbers:refresh-risk`.
- Web: form manual "Usulkan label" (menghubungkan ke kanal yang sama).
- Verifikasi: Feature tests (anti-abuse, consent, withdraw, agregasi) + E2E.

## Fase 4 — Moderasi & Anti-Abuse Lanjutan
- Moderasi kontribusi: approve/reject/withdraw oleh admin; masuk `moderation_logs` + `audit_logs`.
- Suspend user (sudah ada guard `user.active`; lengkapi UI/API).
- Batas frekuensi report/contribution per nomor; genjot deteksi duplikat.
- Verifikasi: Feature tests admin + E2E admin.

## Fase 5 — ML Dataset & Endpoint Internal
- Struktur `ml-service/datasets/`, `ml-service/notebooks/`, `ml-service/training/`; file metadata (source, license, version, split train/val/test, preprocessing).
- Semua dataset sintetis diberi penanda **SYNTHETIC — BUKAN DATA PRODUKSI**.
- Endpoint internal: `POST /ml/retrain` (token), `GET /ml/model/version`.
- Test metrik: accuracy, precision, recall, F1, ROC-AUC, PR-AUC, confusion matrix (pytest).

## Fase 6 — Risk Engine Configurable
- Bobot `[0.40, 0.25, 0.20, 0.15]` dipindah dari `RiskEngine::weights()` ke config `config/risk.php` + env `RISK_W_XGBOOST`, `RISK_W_INDOBERT`, `RISK_W_COMMUNITY`, `RISK_W_RULE`.
- Tambah faktor kontribusi nametag ke explainability (factors).
- Normalisasi bobot bila IndoBERT null (proporsional, tetap).
- Verifikasi: unit tests weights + update docs.

## Fase 7 — Redis, Queue, Security, Testing
- Queue untuk refresh skor async (sudah ada `RecalculateRisk` job; pastikan pakai broker Redis bila perlu).
- Tes policy/authz, throttle, validasi.
- Vitest halaman baru (privacy/terms/contribution), E2E tambahan.

## Fase 8 — Docker/Production + CI Flutter
- Update `docs/deploy-vps.md`: HTTPS (certbot), backup MySQL, memastikan `ml-service` tidak terbuka ke publik.
- CI: job Flutter (`flutter analyze` + `flutter test`) memakai image Flutter resmi.
- Dry-run deploy local produksi.

## Mobile (Flutter) — Paralel
- Scaffold `mobile/`: flutter_contacts, dio, riverpod, gorouter.
- Layar: onboarding **consent privacy** → login/register → permission Contacts (tujuan jelas) → **sync otomatis kontak** (normalisasi, dedupe) → search + number profile → report/review/tag → history → settings (tarik kontribusi/opt-out).
- Widget/unit test: normalisasi nomor, build payload kontribusi, error/offline handling.
- Validasi via CI Docker (tanpa SDK lokal).

## Aturan Umum
- Tanpa rebranding: branding CekNO tetap.
- Tidak ada data yang dianggap fakta resmi; UI selalu "… — N kontribusi komunitas".
- Data ML sintetis selalu ditandai.
- Privacy-first: consent version, data minimization, opt-out, penarikan kontribusi.