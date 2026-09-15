#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."

# .env dari template sekali saja (jangan timpa konfigurasi yang ada)
if [ ! -f .env ]; then
  cp .env.docker .env
  echo "==> .env dibuat dari .env.docker. Atur APP_KEY & password DB di .env!"
  exit 1
fi

# Ambil variabel yang relevan
APP_PORT="$(grep -E '^APP_PORT=' .env | cut -d= -f2 | tr -d '"' || echo 8000)"

echo "==> Membangun image CekNO..."
docker compose build

echo "==> Menjalankan mysql & redis..."
docker compose up -d mysql redis

echo "==> Menunggu database sehat..."
until [[ "$(docker inspect -f '{{if .State.Health}}{{.State.Health.Status}}{{else}}running{{end}}' $(docker compose ps -q mysql) 2>/dev/null)" == "healthy" ]]; do
  sleep 2
done

echo "==> Migrasi & seed demo..."
docker compose run --rm backend php artisan migrate --force
# Seed hanya saat first run; skip bila tabel sudah terisi
docker compose run --rm backend php artisan db:seed --class=DemoSeeder --force || true

echo "==> Menyalakan seluruh stack..."
docker compose up -d

echo "==> CekNO aktif di http://localhost:${APP_PORT:-8000}"