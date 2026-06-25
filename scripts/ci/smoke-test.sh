#!/usr/bin/env bash
set -euo pipefail
GATEWAY="${GATEWAY_URL:-http://localhost:8080}"
MAX_ATTEMPTS="${SMOKE_MAX_ATTEMPTS:-60}"
SLEEP_SEC="${SMOKE_SLEEP_SEC:-5}"
wait_for_url() {
  local url="$1" label="$2" attempt=1
  echo "Čekam ${label}: ${url}"
  until curl -sf --max-time 5 "$url" >/dev/null 2>&1; do
    if [ "$attempt" -ge "$MAX_ATTEMPTS" ]; then
      echo "TIMEOUT: ${label}"; return 1
    fi
    attempt=$((attempt + 1)); sleep "$SLEEP_SEC"
  done
  echo "OK: ${label}"
}
wait_for_url "${GATEWAY}/health" "API Gateway"
wait_for_url "${GATEWAY}/api/health" "Identity"
docker compose exec -T chat-service node -e "fetch('http://127.0.0.1:8003/health').then(r=>r.ok?process.exit(0):process.exit(1)).catch(()=>process.exit(1))"
echo "OK: Chat Service"
docker compose exec -T event-processor node -e "fetch('http://127.0.0.1:8010/health').then(r=>r.ok?process.exit(0):process.exit(1)).catch(()=>process.exit(1))"
echo "OK: Event Processor"
status=$(curl -s -o /dev/null -w "%{http_code}" -X POST "${GATEWAY}/api/login" -H "Content-Type: application/json" -d '{"email":"valid@example.com"}')
[ "$status" = "422" ] || { echo "FAIL login validation: $status"; exit 1; }
echo "OK: Login validacija"
