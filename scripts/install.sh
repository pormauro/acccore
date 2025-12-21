#!/usr/bin/env bash
set -e

log() { echo "[INSTALL] $1"; }
fail() { echo "[ERROR] $1"; exit 1; }

log "Checking PHP binary"
command -v php >/dev/null || fail "PHP not installed"

PHP_VERSION=$(php -r 'echo PHP_VERSION;')
log "PHP version: $PHP_VERSION"

log "Checking PHP extensions"
REQUIRED_EXTENSIONS=(pdo_pgsql mbstring openssl json ctype xml)
for ext in "${REQUIRED_EXTENSIONS[@]}"; do
  php -m | grep -qi "$ext" || fail "Missing PHP extension: $ext"
done

log "Checking Composer"
command -v composer >/dev/null || fail "Composer not installed"

log "Installing PHP dependencies"
composer install --no-interaction

if [ ! -f .env ]; then
  log "Creating .env file"
  cp .env.example .env
  php artisan key:generate
else
  log ".env already exists, skipping"
fi

log "Verifying database connectivity"
php artisan migrate:status >/dev/null 2>&1 || \
  fail "Laravel cannot connect to PostgreSQL (check .env credentials)"

log "FASE 0 environment validation completed successfully"
