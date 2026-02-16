#!/bin/sh
set -e

# Ako .env ne postoji, kreiraj minimalni iz env varijabli (docker-compose)
if [ ! -f .env ]; then
  cat > .env << EOF
APP_NAME="${APP_NAME:-Laravel}"
APP_ENV="${APP_ENV:-production}"
APP_KEY="${APP_KEY:-}"
APP_DEBUG="${APP_DEBUG:-false}"
APP_URL="${APP_URL:-http://localhost:8000}"
DB_CONNECTION="${DB_CONNECTION:-sqlite}"
DB_DATABASE="${DB_DATABASE:-/var/www/html/storage/database.sqlite}"
CACHE_DRIVER=file
SESSION_DRIVER=file
EOF
  if [ -z "$APP_KEY" ] && [ -f artisan ]; then
    php artisan key:generate --force
  fi
fi

# SQLite fajl
if [ "$DB_CONNECTION" = "sqlite" ]; then
  db_path="${DB_DATABASE:-/var/www/html/storage/database.sqlite}"
  mkdir -p "$(dirname "$db_path")"
  touch "$db_path"
  chmod 664 "$db_path"
fi

# Migracije (custom putanja u projektu)
php artisan migrate --path=src/Infrastructure/Database/Migrations --force --no-interaction 2>/dev/null || true

exec "$@"
