#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."

# 1. Buat .env dari template sekali saja (jangan timpa)
if [ ! -f .env ]; then
  cp .env.docker .env
  echo "==> .env dibuat dari .env.docker. Mengisi APP_KEY..."
fi

# 2. Generate APP_KEY jika masih placeholder
if grep -q 'GENERATE_WITH' .env; then
  NEW_KEY=$(openssl rand -base64 32)
  sed -i.bak "s|^APP_KEY=.*|APP_KEY=base64:${NEW_KEY}|" .env
  rm -f .env.bak
  echo "==> APP_KEY berhasil digenerate."
elif grep -qE '^APP_KEY=base64:[A-Za-z0-9+/]{40,}={0,2}$' .env; then
  echo "==> APP_KEY sudah ada, skip."
else
  echo "==> APP_KEY belum diisi. Generate manual: openssl rand -base64 32"
  echo "    Lalu tambahkan: APP_KEY=base64:<hasil> ke .env"
  exit 1
fi

# 3. Bangun semua image (nginx, backend, ml-service)
echo "==> Membangun image CekNO..."
docker compose build

# 4. Jalankan MySQL & Redis, tunggu hingga sehat
echo "==> Menyalakan mysql & redis..."
docker compose up -d mysql redis

echo "==> Menunggu database & cache sehat..."
until [[ "$(docker inspect -f '{{if .State.Health}}{{.State.Health.Status}}{{else}}running{{end}}' $(docker compose ps -q mysql) 2>/dev/null)" == "healthy" ]]; do
  sleep 2
done
until [[ "$(docker inspect -f '{{if .State.Health}}{{.State.Health.Status}}{{else}}running{{end}}' $(docker compose ps -q redis) 2>/dev/null)" == "healthy" ]]; do
  sleep 2
done

# 5. Jalankan migrasi & seed demo
echo "==> Menjalankan migrasi..."
docker compose run --rm backend php artisan migrate --force
echo "==> Menjalankan seed data demo..."
docker compose run --rm backend php artisan db:seed --class=DemoSeeder --force || true

# 6. Nyalakan seluruh stack
echo "==> Menyalakan seluruh stack..."
docker compose up -d

APP_PORT=$(grep -E '^APP_PORT=' .env | cut -d= -f2 | tr -d '"' || echo 8000)
echo ""
echo "===================================================="
echo "  CekNO berhasil dijalankan!"
echo "  Aplikasi:  http://localhost:${APP_PORT}"
echo "  API Docs:  http://localhost:${APP_PORT}/api/numbers/search?phone=081299887761"
echo "  ML Health: http://localhost:${APP_PORT}/ml/health"
echo "  Admin:     admin@cekno.id / password"
echo "===================================================="
