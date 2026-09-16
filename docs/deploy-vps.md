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
git clone https://github.com/handispramudyo-hub/Cekno.git && cd Cekno
cp .env.docker .env
# APP_KEY akan di-generate oleh boot.sh.
# Jika ingin manual: sed -i "s|APP_KEY=.*|APP_KEY=base64:$(openssl rand -base64 32)|" .env
```

Edit `.env` bila perlu:

| Variabel | Contoh |
|---|---|
| `APP_PORT` | `80` (atau `8000` di belakang reverse proxy) |
| `APP_KEY` | otomatis terisi oleh boot.sh; isi manual jika ingin |
| `DB_PASSWORD` / `DB_ROOT_PASSWORD` | ganti dengan nilai kuat! |

## 2. Bangun & jalankan

```bash
chmod +x scripts/boot.sh
./scripts/boot.sh
```

`boot.sh` melakukan:
1. Generate `APP_KEY` jika belum ada
2. `docker compose build` (backend + nginx + ml-service; dist frontend di-bake ke nginx)
3. menunggu `mysql` & `redis` sehat
4. `migrate --force` + `db:seed --class=DemoSeeder --force` (data demo; hapus baris seed untuk produksi bersih)
5. `docker compose up -d`
6. cetak URL akses

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
curl -s http://localhost/api/ml/health        # -> {"status":"ok"}
curl -s http://localhost/api/numbers/search?phone=081299887761 | head -c 300
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
| `502 Bad Gateway` pada `/api` | `docker compose logs backend`; pastikan `APP_KEY` terisi & vendor ada di image |
| MySQL tak sehat | `docker compose logs mysql`; _volume lama dengan password beda_ → hapus volume `mysql-data` lalu up ulang |
| Skor risiko tetap `low`/null | `docker compose logs ml-service`; pastikan `ML_SERVICE_URL` benar dan `/ml/health` ok |
| Frontend kosong (404 SPA) | cache CDN/browser; nginx SPA fallback sudah aktif; pastikan build frontend sukses |
| Healthcheck backend gagal | `docker compose ps`: pastikan fpm bind port 9000; cek `docker compose logs backend` |
