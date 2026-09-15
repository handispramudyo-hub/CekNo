PRD — CekNO
Community-Based Phone Number Reputation & Fraud Risk Detection Platform

Versi: 1.0
Platform: Web
Target: MVP → Production
Role: User & Admin
Core AI: XGBoost + IndoBERT
Backend: Laravel 12
Frontend: React + Vite
Database: MySQL
ML Service: Python + FastAPI

1. Product Overview

CekNO adalah platform berbasis web yang memungkinkan pengguna mencari nomor telepon dan memperoleh informasi reputasi berdasarkan:

Community tag
Riwayat laporan
Riwayat pencarian
Review/komentar
Kategori laporan
Pola aktivitas
Machine Learning
NLP

Sistem menghasilkan Risk Score 0–100 yang menunjukkan tingkat risiko nomor tersebut.

CekNO tidak bertujuan menentukan identitas pemilik nomor secara pasti. Sistem menampilkan informasi reputasi dan pengalaman komunitas yang telah melalui mekanisme validasi/moderasi.

2. Problem Statement

Pengguna sering menerima:

telepon dari nomor tidak dikenal,
SMS mencurigakan,
pesan WhatsApp,
penawaran palsu,
phishing,
penipuan transaksi,
telemarketing agresif.

Masalahnya, pengguna sulit mengetahui apakah nomor tersebut pernah dilaporkan orang lain.

Solusi CekNO:

Nomor Telepon
↓
Community Data
↓
Reports + Tags + Reviews
↓
Machine Learning
↓
Risk Analysis
↓
Risk Score 3. Product Goals
Primary Goals
Memungkinkan pencarian nomor telepon.
Menampilkan reputasi nomor.
Menampilkan community tags.
Menampilkan riwayat laporan.
Menganalisis komentar pengguna.
Menghasilkan risk score.
Mencegah penyalahgunaan melalui moderation.
Menyediakan explainable risk assessment.
Non-Goals

Untuk MVP:

Tidak membaca kontak pengguna.
Tidak membaca WhatsApp.
Tidak membaca SMS.
Tidak melakukan caller ID realtime.
Tidak menentukan nama legal pemilik nomor.
Tidak melakukan scraping data pribadi. 4. User Roles
USER

Dapat:

Search Number
View Number Profile
Add Tag
Submit Report
Write Review
View History
Manage Profile
ADMIN

Dapat:

Manage Users
Manage Numbers
Moderate Reports
Moderate Tags
Moderate Reviews
Manage Categories
View Analytics
Manage ML Model
View Audit Logs 5. High-Level Architecture
┌──────────────────┐
│ React │
│ Frontend │
└────────┬─────────┘
│
REST API
│
┌────────▼─────────┐
│ Laravel │
│ Backend │
└──────┬─────┬──────┘
│ │
┌────────┘ └─────────┐
▼ ▼
┌─────────┐ ┌───────────┐
│ MySQL │ │ ML Service │
│ Database│ │ FastAPI │
└─────────┘ └─────┬─────┘
│
┌────────────┴────────────┐
▼ ▼
XGBoost IndoBERT
Model NLP Model
│ │
└────────────┬────────────┘
▼
Risk Engine
│
▼
Risk Score 6. Technology Stack
Frontend
React 19
Vite
Tailwind CSS
React Router
TanStack Query
Axios
Recharts
SweetAlert2
React Hot Toast
Backend
Laravel 12
PHP 8.2+
Laravel Sanctum
REST API
Laravel Queue
Laravel Scheduler
Database
MySQL 8
AI / ML
Python 3.11+
FastAPI
scikit-learn
XGBoost
Pandas
NumPy
Transformers
PyTorch
IndoBERT
Infrastructure
Git
GitHub
Docker
Nginx
Linux VPS 7. Database Design
users
id
name
email
password
phone
role
status
email_verified_at
created_at
updated_at

Role:

user
admin 8. phone_numbers
id
phone_number
country_code
normalized_number
risk_score
risk_level
total_reports
total_reviews
total_tags
search_count
status
created_at
updated_at

Contoh:

phone_number:
081234567890

normalized_number:
+6281234567890

risk_score:
82

risk_level:
high

normalized_number harus memiliki unique index.

9. tags
   id
   name
   slug
   category
   status
   created_at
   updated_at

Contoh:

Sales
Kurir
Telemarketing
Bank
Customer Service
Spam
Penipuan
Phishing 10. phone_tags

Relasi many-to-many:

id
phone_number_id
tag_id
user_id
status
created_at

Contoh:

User A
→ +6281234567890
→ Tag "Spam" 11. reports
id
phone_number_id
user_id
category
description
evidence
status
moderated_by
moderated_at
created_at
updated_at

Category:

spam
fraud
phishing
telemarketing
loan
harassment
other

Status:

pending
approved
rejected 12. reviews
id
phone_number_id
user_id
rating
comment
sentiment
fraud_probability
status
moderated_by
moderated_at
created_at
updated_at

AI dapat mengisi:

sentiment:
negative

fraud_probability:
0.91 13. search_histories
id
user_id
phone_number_id
created_at

Untuk guest, jangan menyimpan identitas pribadi secara permanen; gunakan mekanisme rate-limit/session/anonymous telemetry yang minimal bila memang dibutuhkan.

14. risk_assessments

Ini tabel penting untuk AI.

id
phone_number_id
model_version
xgboost_probability
indobert_probability
community_score
rule_score
final_score
risk_level
explanation
created_at

Contoh:

xgboost_probability = 0.89
indobert_probability = 0.91
community_score = 0.82
rule_score = 0.88

final_score = 91 15. ml_predictions

Untuk menyimpan histori prediksi:

id
phone_number_id
model_type
model_version
input_features
prediction
confidence
created_at

Model:

xgboost
indobert 16. moderation_logs
id
admin_id
target_type
target_id
action
reason
created_at

Contoh:

admin_id: 1
target_type: report
target_id: 1002
action: approved
reason: Evidence valid 17. audit_logs

Semua aktivitas sensitif dicatat.

id
user_id
action
ip_address
user_agent
metadata
created_at

17b. categories (dibutuhkan untuk fitur Admin "Manage Categories")

id
name
slug
description
risk_weight
status
created_at
updated_at

Kategori default: spam, fraud, phishing, telemarketing, loan, harassment, other.

17c. ml_models (registry untuk ML lifecycle / Admin "Manage ML Model")

id
algorithm            (xgboost | indobert | logistic_regression | random_forest | lightgbm | catboost)
model_version        (mis. 1.0.0)
dataset_version
training_date
metrics              (JSON: accuracy, precision, recall, f1, roc_auc, pr_auc, confusion_matrix)
status               (experiment | active | retired)
model_path
storage_size_kb
created_at
updated_at

18. Database Relationship
USER
│
├──────────────┐
│ │
▼ ▼
REPORT REVIEW
│ │
└──────┬───────┘
▼
PHONE_NUMBER
│
├──────── TAG
│
├──────── RISK_ASSESSMENT
│
├──────── ML_PREDICTION
│
└──────── SEARCH_HISTORY

ML_MODELS ← dipakai RISK_ASSESSMENT (model_version)
CATEGORIES ← dipakai REPORTS & TAGS (kategori terkelola)
ADMIN ── AUDIT_LOG & MODERATION_LOG 19. Search Workflow
User
↓
Input Number
↓
Frontend Validation
↓
Laravel API
↓
Normalize Number
↓
Rate Limit
↓
Find Phone Number
↓
Get:
├─ Tags
├─ Reports
├─ Reviews
├─ Statistics
└─ Risk Assessment
↓
Return JSON
↓
React
↓
Display Number Profile 20. Number Normalization

Semua format harus menjadi:

+6281234567890

Contoh:

081234567890
+6281234567890
6281234567890

→

+6281234567890

Backend wajib melakukan normalisasi sebelum query database.

21. Community Data Pipeline
    User
    ↓
    Submit Report
    ↓
    Pending
    ↓
    Admin Moderation
    ↓
    Approved
    ↓
    Database
    ↓
    Feature Engineering
    ↓
    ML

Report yang ditolak tidak boleh digunakan sebagai evidence utama untuk menaikkan risk score.

22. Feature Engineering

XGBoost menggunakan fitur seperti:

total_reports
fraud_reports
spam_reports
phishing_reports
telemarketing_reports
unique_reporters
total_reviews
average_rating
negative_reviews
positive_reviews
fraud_related_reviews
search_frequency
recent_reports
report_growth_rate
tag_count

Contoh input:

fraud_reports = 8
spam_reports = 15
unique_reporters = 21
average_rating = 1.8
negative_reviews = 18
search_frequency = 300
recent_reports = 7 23. XGBoost

Model:

XGBoost Classifier

Target:

0 = Legitimate
1 = Fraud/Risky

Output:

Fraud Probability

Contoh:

0.87

Kemudian:

87% 24. IndoBERT

Input:

"Dia mengaku dari bank dan meminta kode OTP."

Pipeline:

Raw Comment
↓
Cleaning
↓
Tokenization
↓
IndoBERT
↓
Classification

Output:

Fraud: 0.91
Spam: 0.07
Legitimate: 0.02 25. Dataset

Dataset ideal memiliki dua bagian.

Dataset Tabular
phone_id
reports
tags
reviews
rating
search_frequency
activity
label
Dataset Text
comment
category
label

Label:

legitimate
spam
fraud
phishing
telemarketing

Untuk penelitian, dataset harus berasal dari sumber yang legal dan memiliki dasar penggunaan yang jelas. Data aplikasi produksi sebaiknya dipisahkan dari dataset eksperimen dan dianonimkan bila diperlukan.

26. Training Pipeline
    Raw Dataset
    ↓
    Data Cleaning
    ↓
    Data Validation
    ↓
    Feature Engineering
    ↓
    Train / Validation / Test Split
    ↓
    Model Training
    ↓
    Hyperparameter Tuning
    ↓
    Evaluation
    ↓
    Model Selection
    ↓
    Model Versioning
    ↓
    Deploy
27. Evaluasi XGBoost

Gunakan:

Accuracy
Precision
Recall
F1-Score
ROC-AUC
PR-AUC
Confusion Matrix

Prioritas:

1. Recall

Agar kasus fraud tidak banyak lolos.

2. Precision

Agar nomor normal tidak terlalu sering ditandai sebagai fraud.

3. F1

Untuk mencari keseimbangan.

28. Evaluasi IndoBERT

Gunakan:

Accuracy
Precision
Recall
F1
Confusion Matrix

Terutama per kelas:

Fraud
Spam
Phishing
Legitimate 29. Risk Engine

Ini bukan model AI murni.

Risk Engine menggabungkan:

XGBoost

- IndoBERT
- Community Evidence
- Rule Engine

Contoh:

XGBoost = 87
IndoBERT = 91
Community = 82
Rules = 88

Formula awal (penjumlahan berbobot):

Final Score =
(XGBoost × 0.40)
+ (IndoBERT × 0.25)
+ (Community × 0.20)
+ (Rules × 0.15)

Khusus saat nomor tidak memiliki review/teks sehingga IndoBERT tidak dapat menghasilkan skor:
bobot IndoBERT (0.25) direnormalisasi proporsional ke komponen lain, sehingga bobot tetap berjumlah 1.0.

Contoh:

87 × 0.40 = 34.8
91 × 0.25 = 22.75
82 × 0.20 = 16.4
88 × 0.15 = 13.2

Total = 87.15

Hasil:

87/100
HIGH RISK

Bobot ini harus diperlakukan sebagai parameter eksperimen, bukan angka yang dianggap benar sejak awal. Pada penelitian, bobot sebaiknya diuji/dituning menggunakan validation set.

29b. Definisi Community Score & Rule Score

Nilai komponen ini dihitung HANYA dari data berstatus approved (report rejected tidak ikut).

community_score (0-100):

100 × (0.40 × min(r / 20, 1) + 0.25 × min(u / 15, 1) + 0.20 × min(t / 5, 1) + 0.15 × neg_ratio)

r          = jumlah approved report berkategori berisiko (fraud, spam, phishing, telemarketing, loan, harassment)
u          = jumlah pelapor unik (approved reports)
t          = jumlah tag berisiko (kategori penipuan/spam/phishing/telemarketing)
neg_ratio  = proporsi review yang disetujui dengan rating ≤ 2 atau sentiment negatif

rule_score (0-100), akumulasi ter-cap 100:

+40 jika ≥ 3 approved report kategori fraud/phishing
+20 jika unique_reporters ≥ 8
+15 jika average_rating ≤ 2.0
+10 jika recent_reports (approved, 7 hari terakhir) ≥ 3
+10 jika search_count ≥ 200
+5  jika ada tag penipuan/spam/phishing

Explainability menggunakan faktor-faktor di atas (Approved fraud reports, unique_reporters, negative_reviews, recent_reports 7 hari, search_frequency) untuk menghasilkan daftar "Kenapa berisiko?".

30. Risk Classification
    0–24
    LOW

25–49
CAUTION

50–74
RISKY

75–100
HIGH RISK 31. Explainable Risk

Sistem harus menghasilkan alasan.

Contoh:

Risk Score: 87

Faktor utama:

✓ 8 laporan penipuan yang disetujui
✓ 21 pengguna berbeda melaporkan
✓ 18 review bernada negatif
✓ 76% review terindikasi fraud
✓ 7 laporan terjadi dalam 7 hari terakhir

Bukan:

"AI mengatakan nomor ini penipu."

Melainkan:

"Nomor ini memiliki risiko tinggi berdasarkan pola laporan dan informasi komunitas."

32. API
    Authentication
    POST /api/auth/register
    POST /api/auth/login
    POST /api/auth/logout
    GET /api/auth/me
    Number
    GET /api/numbers/search
    GET /api/numbers/{id}
    Tags
    GET /api/numbers/{id}/tags
    POST /api/numbers/{id}/tags
    Reports
    POST /api/numbers/{id}/reports
    GET /api/numbers/{id}/reports
    Reviews
    POST /api/numbers/{id}/reviews
    GET /api/numbers/{id}/reviews
    User
    GET /api/user/history
    GET /api/user/reports
    GET /api/user/reviews
    GET /api/user/tags
    Review Extra
    GET /api/numbers/{id}/reviews   (public, approved)
    Admin (semua dilindungi role admin)
    GET    /api/admin/dashboard
    GET    /api/admin/users
    PATCH  /api/admin/users/{id}/status      (aktif/nonaktif)
    PATCH  /api/admin/users/{id}/role
    GET    /api/admin/numbers
    GET    /api/admin/reports                (filter status)
    POST   /api/admin/reports/{id}/moderate  (approve/reject + reason)
    GET    /api/admin/reviews
    POST   /api/admin/reviews/{id}/moderate
    GET    /api/admin/tags
    POST   /api/admin/tags/{id}/moderate
    GET|POST|PUT|DELETE /api/admin/categories
    GET    /api/admin/analytics
    GET    /api/admin/ml-models
    POST   /api/admin/ml-models/{id}/activate
    GET    /api/admin/audit-logs
33. ML API

Laravel berkomunikasi dengan Python service.

Prediction
POST /ml/predict/risk

Request:

{
"total_reports": 20,
"fraud_reports": 8,
"spam_reports": 10,
"unique_reporters": 15,
"average_rating": 1.8
}

Response:

{
"model": "xgboost",
"version": "1.0.0",
"fraud_probability": 0.87
}
NLP API
POST /ml/analyze/comment

Request:

{
"text": "Mengaku dari bank dan meminta OTP"
}

Response:

{
"model": "indobert",
"fraud_probability": 0.91,
"category": "phishing"
} 34. Admin Workflow
User Report
↓
Admin Dashboard
↓
Pending
↓
Review Evidence
↓
Approve / Reject
↓
Approved Data
↓
Risk Engine
↓
Recalculate 35. Frontend Pages
Public
/
/search
/number/:id
/login
/register
User
/dashboard
/history
/reports
/reviews
/tags
/profile
/settings
Admin
/admin
/admin/users
/admin/numbers
/admin/reports
/admin/reviews
/admin/tags
/admin/moderation
/admin/analytics
/admin/ml
/admin/audit-logs 36. Number Detail UI
+62 812-3456-7890

🔴 87/100
HIGH RISK

────────────────────

NAMETAGS

[Spam]
[Telemarketing]
[Sales]

────────────────────

REPORTS

Penipuan 8
Spam 10
Phishing 2

────────────────────

COMMUNITY

21 users reported this number

────────────────────

AI ANALYSIS

Fraud probability
87%

Phishing indicators
91%

────────────────────

REVIEWS

⭐⭐☆☆☆

"Mengaku dari bank..."

AI:
Phishing
91%

────────────────────

[ Laporkan Nomor ]
[ Tambahkan Tag ] 37. Security
Authentication

Laravel Sanctum.

Password
Argon2id / bcrypt
API
Rate Limiting
Authentication
Authorization
Validation
CSRF protection
ML

ML service tidak boleh langsung dibuka ke internet.

Arsitektur:

Internet
↓
Nginx
↓
Laravel
↓
Internal ML API 38. Anti-Abuse

Sistem harus menangani:

Fake Reports
1 user
→ 100 reports

Dicegah dengan:

rate limit,
account reputation,
duplicate detection,
moderation.
Duplicate Reports

Jika banyak user melaporkan nomor yang sama dengan isi identik, sistem dapat mendeteksi pola tersebut.

Coordinated Abuse

Aktivitas mencurigakan:

10 account
↓
dibuat dalam waktu berdekatan
↓
melaporkan nomor yang sama

dapat diberi flag:

suspicious_activity 39. Privacy

CekNO harus mengikuti prinsip:

Data minimization.

Jangan mengumpulkan:

❌ Contact list
❌ SMS
❌ WhatsApp messages
❌ Private conversations
❌ Unnecessary personal data

Yang dikumpulkan:

✓ Account
✓ Community report
✓ Tags
✓ Reviews
✓ Search activity yang diperlukan
✓ Moderation data 40. ML Model Lifecycle

Model tidak langsung dianggap final.

Dataset
↓
Training
↓
Evaluation
↓
Model v1
↓
Production
↓
Monitoring
↓
New Data
↓
Retraining
↓
Model v2

Database:

model_version
training_date
dataset_version
metrics
algorithm
status 41. Model Comparison

Sebelum menentukan model final:

                    Accuracy Precision Recall F1

Logistic Regression
Random Forest
XGBoost
LightGBM
CatBoost

Kemudian pilih model berdasarkan hasil eksperimen, bukan asumsi.

Misalnya hasil penelitian:

XGBoost
Accuracy = 94%
Precision = 92%
Recall = 95%
F1 = 93%

Barulah kita menyimpulkan XGBoost sebagai model terbaik untuk dataset tersebut.

42. Development Phase
    Phase 1 — Foundation
    Repository
    Database
    Laravel
    React
    Authentication
    Phase 2 — Core
    Number Search
    Number Profile
    Tags
    Reports
    Reviews
    Phase 3 — Moderation
    Admin
    Report moderation
    Review moderation
    Audit logs
    Phase 4 — ML
    Dataset
    Feature Engineering
    XGBoost
    IndoBERT
    ML API
    Phase 5 — Risk Engine
    Prediction
    Risk Score
    Explainability
    Phase 6 — Production
    Docker
    Nginx
    VPS
    SSL
    Monitoring
    Backup
43. MVP Acceptance Criteria
    Search
    User dapat mencari nomor.
    Format nomor dinormalisasi.
    Nomor yang sama tidak menghasilkan record berbeda.
    Reports
    User dapat mengirim report.
    Report masuk status pending.
    Admin dapat approve/reject.
    Hanya approved report yang mempengaruhi risk assessment.
    Reviews
    User dapat membuat review.
    Review dapat dimoderasi.
    IndoBERT dapat menganalisis review.
    ML
    XGBoost dapat menghasilkan probability.
    IndoBERT dapat mengklasifikasikan komentar.
    Risk Engine menghasilkan score.
    Model memiliki versioning.
    Hasil prediksi tersimpan.
    Security
    API menggunakan authentication.
    Rate limiting aktif.
    Password di-hash.
    Admin endpoint terlindungi.
44. Struktur Repository

Saya menyarankan monorepo:

cekno/
│
├── frontend/
│ ├── src/
│ ├── components/
│ ├── pages/
│ ├── services/
│ └── hooks/
│
├── backend/
│ ├── app/
│ ├── routes/
│ ├── database/
│ ├── models/
│ └── tests/
│
├── ml-service/
│ ├── app/
│ │ ├── main.py
│ │ ├── routes/
│ │ ├── services/
│ │ └── schemas/
│ │
│ ├── models/
│ │ ├── xgboost/
│ │ └── indobert/
│ │
│ ├── training/
│ ├── datasets/
│ ├── notebooks/
│ └── requirements.txt
│
├── docker/
│
├── docs/
│
└── README.md 45. Final Architecture
┌──────────────┐
│ USER │
└──────┬───────┘
│
▼
┌──────────────┐
│ REACT │
└──────┬───────┘
│
HTTPS
│
▼
┌──────────────┐
│ LARAVEL │
│ REST API │
└───┬──────┬───┘
│ │
│ │
▼ ▼
┌───────┐ ┌────────────┐
│ MySQL │ │ ML SERVICE │
└───────┘ │ FastAPI │
└─────┬──────┘
│
┌──────────┴─────────┐
│ │
▼ ▼
┌─────────┐ ┌─────────┐
│ XGBoost │ │ IndoBERT│
└────┬────┘ └────┬────┘
│ │
└─────────┬──────────┘
▼
RISK ENGINE
│
▼
RISK SCORE
│
┌─────────┼─────────┐
▼ ▼ ▼
SAFE CAUTION HIGH
🎯 Inti sistem CekNO

Jadi kita tidak membuat "database nama pemilik nomor".

Kita membuat:

Database reputasi nomor + community intelligence + AI risk detection.

Sehingga ketika seseorang memasukkan nomor, sistem tidak hanya berkata "nomor ini pernah dilaporkan", tetapi memberikan gambaran:

siapa yang men-tag → jenis tag → riwayat laporan → komentar komunitas → analisis NLP → pola aktivitas → prediksi XGBoost → Risk Score → alasan mengapa nomor tersebut berisiko.

Dan untuk implementasi nyata, XGBoost + IndoBERT jangan langsung diasumsikan paling akurat. PRD ini menetapkan keduanya sebagai kandidat/model utama, tetapi penelitian tetap harus membandingkannya dengan baseline dan melaporkan metrik pada dataset uji yang benar-benar terpisah. Ini justru membuat bagian ML-nya jauh lebih kuat secara akademis.
