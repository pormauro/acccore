#!/usr/bin/env bash
set -e

log() { echo "[INSTALL] $1"; }
fail() { echo "[ERROR] $1"; exit 1; }

log "PHP"
php -v || fail "PHP missing"

log "Composer"
command -v composer >/dev/null || fail "Composer missing"

log "Install dependencies"
composer install --no-interaction --prefer-dist

if [ ! -f .env ]; then
  log "Creating .env"
  cp .env.example .env
  php artisan key:generate
fi

log "Permissions"
chmod -R 775 storage bootstrap/cache || true

log "Clear caches"
php artisan config:clear || true
php artisan cache:clear || true
php artisan view:clear || true

log "Health check"
php artisan route:list | grep health >/dev/null || fail "Health route missing"

log "FASE 0 OK"
