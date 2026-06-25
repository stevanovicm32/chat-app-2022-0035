# Chat Aplikacija — Mikroservisna arhitektura

Web aplikacija za razmenu poruka. Sistem je podeljen na **tri mikroservisa** sa **API Gateway**-jem kao jedinstvenom ulaznom tačkom, u skladu sa **Database per service** patternom.

Nadogradnja u okviru **seminarskog rada**: Event-Driven Architecture (Kafka), CI/CD, bezbednost, monitoring (Prometheus/Grafana) i distribuirani paterni (Saga, Circuit Breaker, CQRS).

Projektna dokumentacija (I domaći): [docs/RNAEP.pdf](docs/RNAEP.pdf)  
Tehnički izveštaj (šta je urađeno i kako): [docs/IZVESTAJ_RADA.md](docs/IZVESTAJ_RADA.md)

## Arhitektura

```
Browser → Frontend (React :3000)
              ↓
         API Gateway (nginx :8080)
         ├── Identity Service (:8001)  → PostgreSQL
         ├── Moderation Service (:8002) → PostgreSQL
         ├── Chat Service (:8003)      → MongoDB
         └── Event Processor (:8010)    → Kafka hybrid consumer/producer
                    ↕
              Apache Kafka (5 topic-a)
```

| Servis | Tehnologija | Baza | Odgovornost |
|--------|-------------|------|-------------|
| **API Gateway** | nginx | — | Rutiranje, CORS, jedinstvena ulazna tačka |
| **Identity Service** | Laravel 10, Sanctum | PostgreSQL | Korisnici, uloge, auth |
| **Moderation Service** | Laravel 10 | PostgreSQL | Prijave, sankcije, Saga korak 1 |
| **Chat Service** | Node.js, Express | MongoDB | Konverzacije, poruke (CQRS write/read) |
| **Event Processor** | Node.js, KafkaJS | — | Hybrid processor (Saga korak 2) |
| **Frontend** | React 18, Vite | — | Korisnički interfejs |

### Eksterni API-ji (open-source)

- **DiceBear** — generisanje avatara (`api.dicebear.com`)
- **Giphy** — GIF pretraga u chatu (`api.giphy.com`)

---

## Seminarski rad — Event-Driven Architecture (Kafka)

Komunikacija između mikroservisa za suspenziju i moderaciju je **asinhrona** preko Apache Kafka.

### Kafka topic-i (5)

| Topic | Producer | Consumer | Opis |
|-------|----------|----------|------|
| `suspenzija-zahtev` | Moderation | Event Processor | Zahtev za suspenziju korisnika |
| `suspenzija-obrada` | Event Processor | Identity | Validiran događaj za primenu |
| `suspenzija-primena` | Identity | Chat | Potvrda primenjene suspenzije |
| `poruka-kreirana` | Chat | Moderation | Nova poruka (auto-prijava) |
| `greska-pri-obradi` | Event Processor, Identity | Moderation | Greška + Saga kompenzacija |

### Hybrid Processor (`services/event-processor`)

**Event Processor** je obavezni hibridni modul:
1. **Consumer** — sluša `suspenzija-zahtev`
2. Poslovna logika — validacija korisnika kod Identity servisa
3. **Producer** — publikuje `suspenzija-obrada` ili `greska-pri-obradi`

### Saga patern (suspenzija korisnika)

```
Moderation → suspenzija-zahtev → Event Processor → suspenzija-obrada → Identity
                                                                          ↓
                                                              suspenzija-primena → Chat
Greška → greska-pri-obradi → Moderation (kompenzacija: otkaz sankcije)
```

**Obrazloženje:** Klasična ACID transakcija nije moguća preko dve baze (moderation_db + identity_db). Saga koordinira korake preko Kafka događaja; pri grešci Moderation servis izvršava **kompenzaciju** (brisanje sankcije, status prijave → `odbijeno`).

### CQRS (Chat servis)

- **Command** — `POST /api/chat/:id/poruka` upisuje u MongoDB (write model)
- **Query** — `GET /api/poruka` čita denormalizovane poruke optimizovane za prikaz
- Promene se propagiraju asinhrono preko `poruka-kreirana` ka Moderation servisu

### Circuit Breaker

U `IdentityClient` (Moderation servis) implementiran je **Circuit Breaker** koji pri višestrukim greškama prekida HTTP pozive ka Identity servisu i sprečava kaskadno otkazivanje.

---

## Bezbednost

| Napad | Zaštita |
|-------|---------|
| **XSS** | `escapeHtml()` / `sanitizeMessage()` u frontendu pre prikaza poruka |
| **CSRF** | Laravel Sanctum + `X-XSRF-TOKEN` header, `/sanctum/csrf-cookie` |
| **IDOR** | Provera vlasništva resursa u `KorisnikController`, chat participant check |
| **CORS** | Striktna lista origin-a na Gateway-u i Chat servisu |
| **SQL Injection** | Eloquent ORM / parametrizovani upiti (bez raw SQL) |

---

## Monitoring (Prometheus + Grafana)

| URL | Opis |
|-----|------|
| http://localhost:9090 | Prometheus |
| http://localhost:3001 | Grafana (admin / admin) |

Mikroservisi izlažu metrike:
- Chat: `/metrics` (HTTP zahtevi, prom-client)
- Event Processor: `/metrics` (Kafka događaji)
- Identity/Moderation: `/api/health`

Dashboard: **ITEH Mikroservisi** (provisioning u `monitoring/grafana/`)

---

## Pokretanje (Docker Compose)

Preduslov: **Docker** i **Docker Compose**.

```bash
docker compose up --build
```

| URL | Opis |
|-----|------|
| http://localhost:3000 | Frontend |
| http://localhost:8080 | API Gateway |
| http://localhost:9090 | Prometheus |
| http://localhost:3001 | Grafana |

### Korisne naredbe

```bash
docker compose up -d --build
docker compose down
docker compose logs -f event-processor
docker compose exec identity-service php artisan db:seed --force
```

## Struktura projekta

```
ITEH/
├── api-gateway/
├── services/
│   ├── chat-service/
│   ├── moderation-service/
│   └── event-processor/      # Hybrid Kafka processor
├── monitoring/
│   ├── prometheus/
│   └── grafana/
├── src/                      # Identity Service
├── frontend/
├── docker-compose.yml
└── docs/RNAEP.pdf
```

## CI/CD

GitHub Actions workflow-i u `.github/workflows/`:

| Workflow | Okidač | Šta radi |
|----------|--------|----------|
| **CI** (`ci.yml`) | `push` / `pull_request` na `main`, `develop` | PHPUnit (Identity + Moderation), frontend build, Docker build svih 6 image-a, integracioni smoke test |
| **CD** (`cd.yml`) | `push` na `main`, ručno | Build i push svih image-a na **GHCR** (`ghcr.io/<owner>/iteh-<servis>`) |

### Image-i na GHCR

- `iteh-api-gateway`
- `iteh-identity-service`
- `iteh-moderation-service`
- `iteh-chat-service`
- `iteh-event-processor`
- `iteh-frontend`

### Lokalno pokretanje testova

```bash
# Identity
composer install && php artisan test

# Moderation
cd services/moderation-service && composer install && php artisan test

# Integracioni smoke (Docker mora biti pokrenut)
docker compose up -d
bash scripts/ci/smoke-test.sh
```

## Projektni menadžment (GitHub Projects)

Razvoj je praćen kroz **GitHub Issues** i **Kanban tablu**:

- [Issues](https://github.com/stevanovicm32/chat-app-2022-0035/issues)
- [Uputstvo za Kanban tablu](docs/GITHUB_PROJECTS.md)

Kolone: **To Do** → **In Progress** → **Done**

```bash
./scripts/setup-github-project.sh   # sinhronizacija Issues
```

---
