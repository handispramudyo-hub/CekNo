# Skema Database

Skema database MySQL 8 untuk CekNO (NUMTAG). Konvensi: nama tabel jamak `snake_case`; FK `constrained()`; `timestamps()`; `softDeletes` sesuai kebutuhan. Migrasi ada di `backend/database/migrations/`.

## 1. Normalisasi Nomor Telepon

- Semua input dinormalisasi ke kanonik `+628123456789` (default country code `+62`).
- Kolom penyimpanan:
  - `phone_number` — input asli (string user).
  - `country_code` — kode negara, default `+62`.
  - `normalized_number` — kanonik, `UNIQUE` (jaringan).
- Normalisasi diterapkan via `Route::bind('phone', ...)` di `AppServiceProvider`.

## 2. Tabel Inti

### users
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint PK | |
| name | string | |
| email | string | unique, lowercased |
| password | string | hashed |
| phone | string nullable | |
| role | enum(user, admin) | default user |
| status | enum(active, suspended) | guard `user.active` |
| email_verified_at | timestamp nullable | verifikasi via email |
| created_at / updated_at | timestamp | |

### phone_numbers
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint PK | |
| phone_number | string | input asli |
| country_code | string(8) | default +62 |
| normalized_number | string(20) | **UNIQUE**, index |
| risk_score | smallint unsigned | 0–100 |
| risk_level | enum(low, caution, risky, high) | index `(risk_level, total_reports)` |
| total_reports / total_reviews / total_tags | int unsigned | statistik agregat |
| search_count | int unsigned | frekuensi pencarian |
| status | enum(active, hidden) | |
| total_contributions / contributor_count | int unsigned | **BARU (Fase 3)** — agregasi nametag |
| created_at / updated_at | timestamp | |

### categories
Daftar kategori laporan dengan bobot risiko (risk_weight). Dipakai `reports.category_id` (optional) + kolom `category` enum.

### tags
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint PK | |
| name / slug | string | slug unik |
| category | string nullable | |
| status | enum(active, inactive) | |

### phone_tags
Relasi nomor–tag dari aksi user (dengan moderasi).
| | Kolom | Catatan |
|---|---|---|
| id / phone_number_id / tag_id / user_id | FK | |
| status | enum(pending, approved, rejected) | |

### reports
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint PK | |
| phone_number_id | FK → phone_numbers | cascade delete |
| user_id | FK → users nullable | nullOnDelete |
| category_id | FK → categories nullable | |
| category | string(32) | fraud, phishing, spam, telemarketing, loan, harassment, other |
| description | text | |
| evidence | text nullable | |
| evidence_type | string(16) | text default |
| description_hash | string(64) index | **anti-duplikat** |
| status | enum(pending, approved, rejected) | index |
| moderated_by | FK → users nullable | |
| moderated_at | timestamp nullable | |
| created_at / updated_at | timestamp | |

### reviews
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint PK | |
| phone_number_id / user_id | FK | |
| rating | tinyint 1–5 | |
| comment | text nullable | |
| sentiment | string nullable | hasil IndoBERT/analyzer |
| category | string nullable | hasil analisis (phishing, dsb.) |
| fraud_probability | float nullable | hasil ML |
| status | enum(pending, approved, rejected) | |
| moderated_by / moderated_at | FK / timestamp | |

### search_histories
`id`, `user_id` (FK), `phone_number_id` (FK), `created_at`. Riwayat pencarian per user.

### risk_assessments
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint PK | |
| phone_number_id | FK | |
| model_version | string | versi model aktif |
| xgboost_probability / indobert_probability | float nullable | |
| community_score / rule_score | float | |
| final_score | smallint | |
| risk_level | enum(low, caution, risky, high) | |
| explanation | text | alasan eksplanabel |
| factors | json nullable | daftar faktor |
| created_at / updated_at | timestamp | |

### ml_predictions
`id`, `phone_number_id` (FK), `model_type` (xgboost/indobert), `model_version`, `input_features` (json), `prediction` (json), `confidence` (float nullable), `created_at`.

### ml_models
Registry model ML: `algorithm`, `model_version`, `is_active`, `performance` (json), `is_synthetic` (bool). Version aktif via scope `active()`.

### moderation_logs
`id`, `admin_id` (FK), `target_type`, `target_id`, `action`, `reason`, `created_at`.

### audit_logs
`id`, `user_id` (FK nullable), `action`, `ip_address`, `user_agent`, `metadata` (json), `created_at`.

### personal_access_tokens / jobs / cache
Standar Laravel (Sanctum tokens, queue jobs, cache).

## 3. Tabel Baru yang Direncanakan (NUMTAG)

### contact_contributions — kontribusi nametag dari kontak/user
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint PK | |
| user_id | FK → users | kontributor |
| phone_number_id | FK → phone_numbers | nomor yang diberi nama |
| label | string | nama kontak / nametag |
| label_normalized | string | lowercase+trim untuk agregasi |
| category | enum(personal, business, sales, service, spam, fraud, other) | |
| consent_version | string | versi consent user |
| status | enum(pending, approved, rejected, **withdrawn**) | moderated |
| created_at / updated_at | timestamp | |

- Constraint: **UNIQUE (user_id, phone_number_id, label_normalized)** → anti duplikat per kontributor.
- Agregasi: `label_normalized` → tampil "label — N kontribusi komunitas".

### consents — persetujuan pengguna
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint PK | |
| user_id | FK → users | |
| version | string | `2026-09-16.1` |
| content_hash | string | hash teks policy yang disetujui |
| accepted_at | timestamp | |
| ip_address / user_agent | string | metadata |

### datasets (opsional) — provenance data ML
`id`, `slug` (unique), `name`, `source`, `license`, `version`, `preprocessing` (text), `splits` (json: train/val/test), `rows`, `is_synthetic` (bool), `created_at/updated_at`.

## 4. Relasi (ringkas)

```
users 1─* contact_contributions *─1 phone_numbers
users 1─* reports  *─1 phone_numbers
users 1─* reviews  *─1 phone_numbers
users 1─* phone_tags *─1 phone_numbers ; phone_tags *─1 tags
users 1─* search_histories *─1 phone_numbers
phone_numbers 1─* risk_assessments, ml_predictions
users 1─* consents ; users (admin) 1─* moderation_logs
```

## 5. Prinsip Anti-Abuse di Skema

- `description_hash` untuk laporan duplikat.
- `UNIQUE(user_id, phone_number_id, label_normalized)` untuk kontribusi duplikat.
- Statistik komunitas berbasis unique contributor (`contributor_count`).
- Moderasi: konten baru selalu `pending` sampai di-approve admin.