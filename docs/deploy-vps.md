# CekNO — Panduan Deploy di VPS

Skrip ringkas untuk menerbitkan CekNO (Laravel + React + FastAPI + MySQL + Redis) ke VPS Ubuntu, lewat Docker Compose.

## 0. Prasyarat server

- Ubuntu 22.04+ LTS (2 vCPU / 2 GB RAM cukup untuk MVP)
- `git`, `docker`, `docker compose plugin`, `curl`

```bash
sudo apt update && sudo apt install -y git curl ca-certificates
curl -fsSL https://get.docker.com | sh
sudo usermod -aG docker $USER
newgrp docker
```

## 1. Clone & konfigurasi

```bash
git clone https://github.com/<org>/cekno.git && cd cekno
cp .env.docker .env
php -r "echo 'APP_KEY='.base64_encode(random_bytes(32)).PHP_EOL;"   # jika php tersedia
# atau gunakan: docker run --rm php:8.2-alpine php -r "echo 'APP_KEY='.base64_encode(random_bytes(32)).PHP_EOL;"
```

Edit `.env` bila perlu:

| Variabel | Contoh |
|---|---|
| `APP_PORT` | `80` (atau `8000` di belakang reverse proxy) |
| `APP_KEY` | hasil generate (wajib) |
| `DB_PASSWORD` / `DB_ROOT_PASSWORD` | ganti dengan nilai kuat! |

## 2. Bangun & jalankan

```bash
./scripts/boot.sh
```

`boot.sh` melakukan:
1. `docker compose build`
2. menunggu `mysql` sehat
3. `migrate --force` + `db:seed --class=DemoSeeder --force` (data demo; hapus baris seed untuk produksi bersih)
4. `docker compose up -d`

Silakan jalankan perintahnya manual jika ingin visual:

```bash
docker compose build
docker compose up -d mysql redis
# tunggu healthy: docker compose ps
docker compose run --rm backend php artisan migrate --force
docker compose run --rm backend php artisan db:seed --class=DemoSeeder --force
docker compose up -d
```

## 3. Verifikasi

```bash
curl -s http://localhost/api/ml/health        # -> {"status":"ok",...}
curl -s "http://localhost/api/numbers/search?phone=081299887761" | head -c 300
```

Buka `http://<server-ip>/` di browser. Login admin default (hanya demo): `admin@cekno.id` / `password`.

> ⚠️ Segera reset password admin setelah deploy ke produksi!

## 4. HTTPS dengan Nginx + Let's Encrypt (opsional tapi disarankan)

Jalankan `app_port=80` di compose, lalu gunakan Nginx host (bukan container) atau Certbot:

```bash
sudo apt install -y nginx certbot python3-certbot-nginx
# buat /etc/nginx/sites-available/cekno:
#   server { listen 80; server_name cekno.example.com;
#            location / { proxy_pass http://127.0.0.1:80; } }
sudo certbot --nginx -d cekno.example.com
```

## 5. Backup database

```bash
# Volume mysql-data tersimpan; cadangkan via mysql client sekali sehari (cron):
0 3 * * * docker compose exec -T mysql mysqldump -u cekno -p<cetak> cekno | gzip > /backup/cekno-$(date +%F).sql.gz
```

Jangan rampungkan password ke cron tanpa file permissions ketat (`chmod 600`).

## 6. Melatih ulang model ML (opsional)

Model XGBoost sudah ditrain saat image di-build (20k baris sintetis, seed 42 → AUC ~0.99). Untuk data nyata:

1. Persiapkan dataset CSV dengan kolom `FEATURES` (lihat `ml-service/app/dataset.py`).
2. Ganti `app/dataset.py::generate` atau tambahkan loader.
3. Rebuild: `docker compose build ml-service && docker compose up -d ml-service`.
4. Versi model otomatis dicatat di `ml-service/models/registry.json`.

## 7. Update aplikasi

```bash
git pull
./scripts/boot.sh
```

## Troubleshooting cepat

| Gejala | Cek |
|---|---|
| `502 Bad Gateway` pada `/api` | `docker compose logs backend`; pastikan `APP_KEY` terisi |
| MySQL tak sehat | `docker compose logs mysql`; _volume lama dengan password beda_ → hapus volume `mysql-data` lalu up ulang |
| Skor risiko tetap `low`/null | `docker compose logs ml-service`; pastikan `ML_SERVICE_URL` benar dan `/ml/health` ok |
| Frontend kosong (404 SPA) | cache CDN/browser; `try_files` sudah menangani fallback |