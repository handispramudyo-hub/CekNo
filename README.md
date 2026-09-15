# CekNO

Platform community-based phone number reputation & fraud risk detection.

CekNO memungkinkan pengguna mencari nomor telepon dan memperoleh informasi reputasi berdasarkan
community tag, riwayat laporan, review, kategori laporan, pola aktivitas, Machine Learning, dan NLP.
Sistem menghasilkan **Risk Score 0–100** yang menunjukkan tingkat risiko nomor tersebut.

> CekNO TIDAK menentukan identitas pemilik nomor. Sistem menampilkan informasi reputasi dan
> pengalaman komunitas yang telah melalui mekanisme validasi/moderasi.

## Arsitektur

```
USER
 │
 ▼
React + Vite + Tailwind
 │  Axios / TanStack Query
 ▼
Laravel 12 REST API (Sanctum, Queue, Scheduler)
 │
 ├─────────────┬─────────────────┐
 ▼             ▼                 ▼
MySQL 8   ML Service (FastAPI)  Redis (cache/queue)
           │
           ├─ XGBoost (data tabular komunitas)
           └─ IndoBERT (analisis teks review/report)
           │
           ▼
      Risk Engine 0-100
```

## Stack

| Bagian | Tech |
|---|---|
| Frontend | React 19, Vite, Tailwind CSS, React Router, TanStack Query, Axios, Recharts, SweetAlert2, React Hot Toast |
| Backend | Laravel 12, PHP 8.2+, Laravel Sanctum, Laravel Queue, Laravel Scheduler |
| Database | MySQL 8 |
| ML / AI | Python 3.12+, FastAPI, scikit-learn, XGBoost, LightGBM, CatBoost, PyTorch, Transformers, IndoBERT, Pandas, NumPy |
| Infrastruktur | Git, GitHub, Docker (opsional), Nginx, Linux VPS |

## Struktur Repository (monorepo)

```
cekno/
├── PRD.md            ← dokumen produk & spesifikasi teknis
├── docs/             ← api.md, db.md, ml.md, deploy.md, mockups/
├── frontend/         ← React + Vite
├── backend/          ← Laravel 12
├── ml-service/       ← FastAPI + training pipeline
├── docker/           ← opsional (deploy)
└── README.md
```

## Mulai Cepat

Lihat masing-masing sub-direktori untuk instruksi setup:

- `backend/README.md`
- `frontend/README.md`
- `ml-service/README.md`

## Status

Project dalam tahap MVP → production. Lihat `PRD.md` untuk detail lengkap,
development phases, dan MVP acceptance criteria.