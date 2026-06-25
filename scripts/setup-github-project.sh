#!/bin/bash
# Podešavanje GitHub Issues za projektni menadžment (Kanban)
set -euo pipefail

REPO_OWNER="stevanovicm32"
REPO_NAME="chat-app-2022-0035"
ROOT="$(cd "$(dirname "$0")/.." && pwd)"

TOKEN=$(printf "protocol=https\nhost=github.com\n\n" | git credential-osxkeychain get 2>/dev/null | awk -F= '/^password=/{print $2}')
if [ -z "$TOKEN" ]; then
  echo "ERROR: GitHub token nije pronađen."
  exit 1
fi

api() {
  local method="$1" path="$2"
  shift 2
  curl -sS -X "$method" \
    -H "Authorization: Bearer $TOKEN" \
    -H "Accept: application/vnd.github+json" \
    -H "X-GitHub-Api-Version: 2022-11-28" \
    "$@" \
    "https://api.github.com/repos/${REPO_OWNER}/${REPO_NAME}${path}"
}

ensure_label() {
  local name="$1" color="$2" desc="$3"
  local code
  code=$(api POST "/labels" -d "$(jq -n --arg n "$name" --arg c "$color" --arg d "$desc" '{name:$n,color:$c,description:$d}')" | jq -r '.message // "ok"')
  if [ "$code" = "ok" ]; then
    echo "  label: $name"
  fi
}

ensure_milestone() {
  local title="$1" desc="$2"
  local existing
  existing=$(api GET "/milestones?state=all" | jq -r --arg t "$title" '.[] | select(.title==$t) | .number' | head -1)
  if [ -n "$existing" ]; then
    echo "  milestone exists: $title (#$existing)"
    return
  fi
  api POST "/milestones" -d "$(jq -n --arg t "$title" --arg d "$desc" '{title:$t,description:$d,state:"open"}')" >/dev/null
  echo "  milestone: $title"
}

close_issue() {
  local num="$1" comment="$2"
  api POST "/issues/${num}/comments" -d "$(jq -n --arg b "$comment" '{body:$b}')" >/dev/null
  api PATCH "/issues/${num}" -d '{"state":"closed"}' >/dev/null
  echo "  closed #$num"
}

create_issue() {
  local title="$1" body="$2" labels="$3" milestone="$4"
  local labels_json
  labels_json=$(echo "$labels" | jq -R 'split(",")')
  local payload
  payload=$(jq -n --arg t "$title" --arg b "$body" --argjson l "$labels_json" --arg m "$milestone" \
    '{title:$t, body:$b, labels:$l, milestone:$m|tonumber}')
  local num
  num=$(api POST "/issues" -d "$payload" | jq -r '.number')
  echo "  created #$num: $title"
  echo "$num"
}

echo "==> Labele (statusi Kanban-a)"
ensure_label "todo" "e4e669" "To Do — zadatak čeka"
ensure_label "in-progress" "fbca04" "In Progress — u razvoju"
ensure_label "done" "0e8a16" "Done — završeno"
ensure_label "microservice" "1d76db" "Mikroservis"
ensure_label "infra" "5319e7" "Infrastruktura"
ensure_label "frontend" "d93f0b" "Frontend"
ensure_label "documentation" "0075ca" "Dokumentacija"
ensure_label "seminar" "b60205" "Seminarski rad"
ensure_label "security" "ee0701" "Bezbednost"

echo "==> Milestone-i"
ensure_milestone "Domaći zadatak II" "Mikroservisna arhitektura, Docker, GitFlow"
ensure_milestone "Seminarski rad" "Kafka, CI/CD, bezbednost, monitoring, paterni"

MILESTONE_II=$(api GET "/milestones?state=open" | jq -r '.[] | select(.title=="Domaći zadatak II") | .number')
MILESTONE_SEM=$(api GET "/milestones?state=open" | jq -r '.[] | select(.title=="Seminarski rad") | .number')

echo "==> Zatvaranje završenih zadataka (Domaći II)"
for num in 2 3 4 5 6 7 8 9 10; do
  close_issue "$num" "✅ Završeno i mergovano u develop/main (GitFlow PR). Status: **Done**."
done

echo "==> Kreiranje zadataka za seminarski rad"
SEMINAR_ISSUES=(
  "Kafka EDA — 5 topic-a i async komunikacija|Asinhrona komunikacija između mikroservisa preko Apache Kafka. Topic-i: suspenzija-zahtev, suspenzija-obrada, suspenzija-primena, poruka-kreirana, greska-pri-obradi.|done,seminar|${MILESTONE_SEM}"
  "Event Processor — hybrid Consumer/Producer|services/event-processor sluša suspenzija-zahtev i publikuje suspenzija-obrada.|done,seminar|${MILESTONE_SEM}"
  "Saga patern — suspenzija sa kompenzacijom|Saga tok za suspenziju korisnika; kompenzacija pri grešci preko greska-pri-obradi.|done,seminar|${MILESTONE_SEM}"
  "Bezbednost — XSS, CSRF, IDOR, CORS, SQL Injection|Implementirane zaštite u frontendu, gateway-u i backend servisima.|done,security,seminar|${MILESTONE_SEM}"
  "Monitoring — Prometheus i Grafana|Metrike, health check-ovi i Grafana dashboard (ITEH Mikroservisi).|done,infra,seminar|${MILESTONE_SEM}"
  "CI/CD — GitHub Actions testovi i Docker build|ci.yml i cd.yml — testovi, build svih image-a, push na GHCR, smoke test.|done,infra,seminar|${MILESTONE_SEM}"
  "Circuit Breaker i CQRS dokumentacija|Circuit Breaker u IdentityClient; CQRS u Chat servisu; dokumentovano u README.|done,seminar|${MILESTONE_SEM}"
)

for item in "${SEMINAR_ISSUES[@]}"; do
  IFS='|' read -r title body labels milestone <<< "$item"
  existing=$(api GET "/issues?state=all" | jq -r --arg t "$title" '.[] | select(.title==$t) | .number' | head -1)
  if [ -n "$existing" ]; then
    echo "  exists #$existing: $title"
    if [[ "$labels" == *"done"* ]]; then
      close_issue "$existing" "✅ Implementirano. Status: **Done**."
    fi
  else
    num=$(create_issue "$title" "$body" "$labels" "$milestone")
    if [[ "$labels" == *"done"* ]]; then
      close_issue "$num" "✅ Implementirano. Status: **Done**."
    fi
  fi
done

echo "==> Otvoreni zadaci (To Do)"
create_issue "Branch protection na main i develop" \
  "Uključiti branch protection: zabrana direktnog push-a, obavezan PR za merge." \
  "todo,infra" "$MILESTONE_II" >/dev/null 2>&1 || true

echo ""
echo "=============================================="
echo "ISSUES su podešeni. Kanban tablu kreiraj ručno:"
echo "https://github.com/users/${REPO_OWNER}/projects/new"
echo ""
echo "1. Template: Board"
echo "2. Naziv: ITEH Mikroservisi"
echo "3. Add item → izaberi repo ${REPO_NAME}"
echo "4. Kolone: To Do | In Progress | Done"
echo "5. Workflow: zatvoreni Issue = Done"
echo "=============================================="
echo "Detalji: docs/GITHUB_PROJECTS.md"
