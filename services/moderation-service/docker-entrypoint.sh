#!/bin/sh
set -e

if [ ! -f .env ]; then
  cat > .env << EOF
APP_NAME="${APP_NAME:-Moderation Service}"
APP_ENV="${APP_ENV:-production}"
APP_KEY="${APP_KEY:-}"
APP_DEBUG="${APP_DEBUG:-false}"
APP_URL="${APP_URL:-http://localhost:8002}"
DB_CONNECTION="${DB_CONNECTION:-pgsql}"
DB_HOST="${DB_HOST:-postgres-moderation}"
DB_PORT="${DB_PORT:-5432}"
DB_DATABASE="${DB_DATABASE:-moderation_db}"
DB_USERNAME="${DB_USERNAME:-moderation}"
DB_PASSWORD="${DB_PASSWORD:-moderation_secret}"
IDENTITY_SERVICE_URL="${IDENTITY_SERVICE_URL:-http://identity-service:8001}"
INTERNAL_API_KEY="${INTERNAL_API_KEY:-internal-dev-key}"
CACHE_DRIVER=file
SESSION_DRIVER=file
EOF
  if [ -z "$APP_KEY" ] && [ -f artisan ]; then
    php artisan key:generate --force
  fi
fi

php artisan migrate --path=src/Infrastructure/Database/Migrations --force --no-interaction 2>/dev/null || true

exec "$@"
