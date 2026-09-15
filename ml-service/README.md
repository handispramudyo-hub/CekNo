# ml-service

Microservice ML untuk CekNO: prediksi risiko penipuan (XGBoost) dan
analisis sentimen/ulasan (IndoBERT, dengan fallback heuristic bila model
berat belum diunduh).

## Endpoint

| Endpoint           | Metode | Deskripsi                                          |
| ------------------ | ------ | -------------------------------------------------- |
| `/ml/health`       | GET    | Status layanan + versi model aktif                 |
| `/ml/predict/risk` | POST   | Prediksi probabilitas penipuan per nomor            |
| `/ml/analyze/comment` | POST | Sentimen + probabilitas penipuan sebuah ulasan  |

Nama endpoint tetap diprefiks `/ml/` sesuai PRD §12 agar konsisten dengan
routing backend (`Illuminate\Routing` -> `MlServiceClient` memanggil
`{ML_SERVICE_URL}/ml/*`).

## Menjalankan (dev)

```bash
python -m venv .venv
.venv\Scripts\activate           # Windows
pip install -r requirements.txt
uvicorn app.main:app --port 8001
```

## Melatih model

```bash
python scripts/train.py
```

Menghasilkan `models/xgboost_{version}.joblib` dan metadata versi di cache
file. Bila tersedia `torch` + `transformers`, skrip juga melatih/memuat
`indobert-base-p1` fine-tuned; jika tidak, fallback heuristic aktif.

## Uji

```bash
pytest -q
```