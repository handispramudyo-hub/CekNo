# Arsitektur CekNO (NUMTAG)

Dokumen ini menjelaskan arsitektur platform identifikasi & reputasi nomor telepon berbasis kontribusi komunitas (nama produk internal: **NUMTAG**). Dokumentasi mengikuti kondisi aktual kode di monorepo ini.

## 1. Gambaran Sistem

```
Mobile (Flutter) ──┐
                   ▼
Web (React+Vite) ──►  nginx ──/api──►      Laravel 12 REST API (Sanctum)
                                     │          │                    │
                                     │          ├── Queue (sync/async, Redis broker)
                                     │          │          │
                                     │          ▼          ▼
                                     │      MySQL 8    Redis 7 (cache + rate limit)
                                     │
                                     │   /ml/* (internal, bukan publik)
                                     ▼
                                 FastAPI ML Service (internal network)
                                 ├── XGBoost (tabular → fraud_probability)
                                 └── IndoBERT (teks review/report → category + probability)
                                       │
                                       ▼
                                  Risk Engine (blend) → Risk Score 0–100
```

## 2. Komponen

| Komponen | Teknologi | Peran |
|---|---|---|
| `frontend/` | React 19, Vite, Tailwind, React Router, TanStack Query, Axios, Recharts, React Hot Toast, SweetAlert2 | Web app publik + admin |
| `backend/` | Laravel 12, PHP 8.2, Sanctum | REST API, otorisasi, moderasi, engine |
| `ml-service/` | Python 3.11+, FastAPI, Pandas, NumPy, Scikit-learn, XGBoost, PyTorch/Transformers, IndoBERT | Prediksi risiko & analisis teks |
| `mysql/` | MySQL 8 | Penyimpanan utama |
| `redis/` | Redis 7 | Cache pencarian, rate-limit, antrian |
| `nginx` | Nginx 1.25 (container) | Reverse proxy + serve SPA + TLS |
| `mobile/` | Flutter, Dart, flutter_contacts, Dio, Riverpod, GoRouter | Mobile app (kontak + pencarian + kontribusi) |

## 3. Alur Pencarian Nomor

1. User memasukkan nomor (barepa pun format: `08123…`, `62812…`, `+62812…`).
2. Laravel menormalkan ke kanonik `+628123456789` (default country `+62`).
3. Rate limit (`throttle:auth.search`).
4. Cek cache Redis (`cekno:number:<normalized>`).
5. Cache miss → query MySQL → kumpulkan tags, named-label komunitas, laporan approved, review, rating, statistik, risk score.
6. Kembalikan JSON → simpan cache.
7. `search_count++`, simpan `search_histories` bila user login.

## 4. Risk Engine

Formula eksperimen (belum final):

```
Final Score = XGBoost × 0.40 + IndoBERT × 0.25 + Community × 0.20 + Rule × 0.15
```

- Bila IndoBERT tidak tersedia (nomor tanpa review berdasar teks), bobot 0.25 direnormalisasi proporsional ke komponen lain (`App\Services\RiskEngine`).
- Bobot saat ini **hardcoded** di `backend/app/Services/RiskEngine.php` (baris 122-124) → akan dijadikan configurable via env (`RISK_W_*`) di Fase 5/6.
- Tingkatan risiko:

| Skor | Level | Label UI |
|---|---|---|
| 0–24 | low | Risiko Rendah |
| 25–49 | caution | Perlu Diwaspadai |
| 50–74 | risky | Risiko Sedang |
| 75–100 | high | Risiko Tinggi |

- Setiap skor memiliki **explanation/factors** (mis. "18 laporan fraud approved, 12 kontributor unik, 74% review negatif") — bukan hanya "AI mendeteksi penipuan".
- Penyimpanan: `risk_assessments` + `ml_predictions` (input feature, prediction, confidence) + `ml_models` (registry versi model aktif).

## 5. Anti-Abuse

- Rate limiting berbasis throttle (register, login, report, review, tag, search, forgot, reset, profile).
- Deteksi duplikat laporan via `description_hash` (kolom `reports.description_hash`).
- Hitung komunitas memakai `unique contributors`, bukan total aksi.
- Moderasi manual oleh admin (approve/reject) sebelum konten tampil publik.
- Audit log (`audit_logs`) + moderation log (`moderation_logs`) untuk semua tindakan admin.
- Kontribusi nametag/kontak direncanakan memiliki limit frekuensi & deteksi duplikat per user/nomor (Fase 3).

## 6. Privasi & Batasan Data

- **Tidak ada** pembacaan SMS, WhatsApp, isi panggilan, foto kontak, alamat, atau email kontak.
- Akses Contacts **hanya di mobile**, setelah consent eksplisit + penjelasan tujuan + `consent_version` tersimpan (`consents`).
- Kontribusi nametag adalah **opini kontributor / kontribusi komunitas**, bukan identitas legal pemilik nomor.
- UI menampilkan agregat "label — N kontribusi komunitas", bukan klaim kepemilikan.
- Mekanisme opt-out & penarikan kontribusi (status `withdrawn`) disediakan.
- Data ML sintetis diberi penanda jelas **SYNTHETIC — BUKAN DATA PRODUKSI**.

## 7. Keamanan

- Sanctum (token-based, header `Authorization: Bearer`).
- Password di-hash (bcrypt/argon).
- Authorization policy & guard `user.active`.
- Validasi request (FormRequest/`$request->validate`), anti SQL injection (Eloquent), XSS via escaping/Tailwind-safe rendering.
- CORS diatur untuk origin web; ML endpoint `/ml/*` **hanya internal** (tidak dipublish port ke internet; hanya di-proxy nginx).
- Environment variables tidak di-commit (`.env` di gitignore; template `backend/.env.example` & `.env.docker`).

## 8. Topologi Deployment

- Docker Compose single-node, 4 container: nginx, backend, ml-service, mysql, redis.
- `ml-service` menggunakan `expose` (bukan `ports`) → tidak dapat diakses dari host/internet.
- Deploy VPS detail: `docs/deploy-vps.md`.

## 9. Monorepo

```
root (CekNo / NUMTAG)
├── backend/       Laravel 12 REST API
├── frontend/      React web (publik + admin)
├── mobile/        Flutter (kontak & inti mobile)
├── ml-service/    FastAPI + XGBoost + IndoBERT
├── docker/        Dockerfile nginx + helper
├── docs/          Dokumentasi
├── e2e/           Playwright end-to-end (Web, vs stack Docker)
├── scripts/       Utility boot/seeding
└── README.md
```