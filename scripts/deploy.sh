#!/usr/bin/env bash
#
# Deploy manual ke production (Hostinger) - dijalankan dari Mac, BUKAN dari GitHub Actions.
# Kenapa bukan GitHub Actions: server Hostinger nge-block koneksi TCP dari IP GitHub Actions
# (Azure) ke port SSH, dites langsung pas setup CI/CD (lihat docs/DECISION_LOG.md 27 Agt 2026).
# Dari Mac ini SSH-nya udah kebukti jalan, jadi deploy dijalanin dari sini aja.
#
# Cara pakai: ./scripts/deploy.sh
# Butuh: composer, npm, rsync, ssh sudah terpasang, dan host alias "keuanganreza-hostinger"
# sudah ada di ~/.ssh/config (lihat docs/DECISION_LOG.md buat detail setup-nya).

set -euo pipefail

REMOTE_HOST="keuanganreza-hostinger"
REMOTE_PATH="/home/u632902628/domains/keuanganreza.my.id/keuangan-app"
PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
BUILD_DIR="$(mktemp -d)"
# mktemp -d bikin folder permission 700 (cuma owner) - kalau gak dibenerin, rsync -a
# nyalin permission itu ke folder app di server, nutup akses web server ke public_html.
chmod 755 "$BUILD_DIR"

cleanup() {
  rm -rf "$BUILD_DIR"
}
trap cleanup EXIT

echo "==> Build dikerjain di folder temp: $BUILD_DIR"
echo "    (biar vendor/node_modules dev di folder project gak keganggu)"

cd "$PROJECT_ROOT"
git archive HEAD | tar -x -C "$BUILD_DIR"

echo "==> Composer install (production, tanpa dev dependency)"
cd "$BUILD_DIR"
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> npm install & build asset"
npm ci
npm run build

echo "==> Siapin .env production"
cp "$PROJECT_ROOT/.env.production" "$BUILD_DIR/.env"

echo "==> Siapin folder storage kosong (biar Laravel gak error kalau belum ada)"
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views \
  storage/framework/testing storage/logs storage/app/public bootstrap/cache

echo "==> Rsync ke server ($REMOTE_HOST:$REMOTE_PATH)"
echo "    .env dan storage/ dikecualikan - itu punya server, jangan ketimpa tiap deploy"
rsync -az --delete \
  --exclude='.env' \
  --exclude='/storage/' \
  --exclude='/node_modules/' \
  --exclude='/tests/' \
  --exclude='phpunit.xml' \
  "$BUILD_DIR/" "$REMOTE_HOST:$REMOTE_PATH/"

echo "==> Migrate + cache di server"
# storage:link Laravel gagal di hosting ini (symlink()/exec() PHP didisable CloudLinux
# CageFS), jadi symlink dibikin manual lewat shell (ln -sfn), bukan artisan.
ssh "$REMOTE_HOST" "
  set -e
  cd '$REMOTE_PATH'
  chmod 755 .
  ln -sfn ../storage/app/public public/storage
  php artisan migrate --force
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
"

echo "==> Deploy selesai."
