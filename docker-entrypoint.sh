#!/bin/sh
set -e

if [ ! -f .env ]; then
  cat > .env << EOF
APP_NAME="${APP_NAME:-Identity Service}"
APP_ENV="${APP_ENV:-production}"
APP_KEY="${APP_KEY:-}"
APP_DEBUG="${APP_DEBUG:-false}"
APP_URL="${APP_URL:-http://localhost:8001}"
DB_CONNECTION="${DB_CONNECTION:-pgsql}"
DB_HOST="${DB_HOST:-postgres-identity}"
DB_PORT="${DB_PORT:-5432}"
DB_DATABASE="${DB_DATABASE:-identity_db}"
DB_USERNAME="${DB_USERNAME:-identity}"
DB_PASSWORD="${DB_PASSWORD:-identity_secret}"
INTERNAL_API_KEY="${INTERNAL_API_KEY:-internal-dev-key}"
CHAT_SERVICE_URL="${CHAT_SERVICE_URL:-http://chat-service:8003}"
CACHE_DRIVER=file
SESSION_DRIVER=file
EOF
  if [ -z "$APP_KEY" ] && [ -f artisan ]; then
    php artisan key:generate --force
  fi
fi

php artisan migrate --path=src/Infrastructure/Database/Migrations/Identity --force --no-interaction 2>/dev/null || \
php artisan migrate --path=src/Infrastructure/Database/Migrations --force --no-interaction 2>/dev/null || true

php artisan db:seed --class=Database\\Seeders\\UlogaSeeder --force --no-interaction 2>/dev/null || true

exec "$@"
