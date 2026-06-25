# Izveštaj o urađenom radu — Chat aplikacija (ITEH)

Ovaj dokument objašnjava **šta je urađeno**, **kako radi** i **gde se šta nalazi** u projektu. Pokriva II domaći zadatak (mikroservisi), seminarski rad (Kafka, bezbednost, monitoring) i GitHub projektni menadžment.

**Repozitorijum:** https://github.com/stevanovicm32/chat-app-2022-0035  
**Tag stabilne verzije (domaći):** `v1.0.0` na `main` grani

---

## 1. Pregled projekta

Aplikacija je chat platforma sa:

- registracijom i prijavom korisnika
- privatnim i grupnim chatovima
- slanjem poruka i GIF-ova
- moderacijom (prijave, sankcije, suspenzija korisnika)
- administratorskim panelom

Početna verzija bila je **monolit** (jedan Laravel backend + React frontend). Zadatak je bio da se prebaci na **mikroservisnu arhitekturu** i kasnije proširi za **seminarski rad**.

---

## 2. II domaći zadatak — šta je urađeno

### 2.1 Arhitektura

Sistem je podeljen na tri mikroservisa + API Gateway + frontend:

```
Browser → Frontend (:3000)
            ↓
       API Gateway nginx (:8080)
       ├── Identity Service (:8001)   → PostgreSQL (identity_db)
       ├── Moderation Service (:8002) → PostgreSQL (moderation_db)
       └── Chat Service (:8003)       → MongoDB (chat_db)
```

**Database per service** — svaki servis ima sopstvenu bazu; nema deljenja tabela između servisa.

| Servis | Folder | Tehnologija |
|--------|--------|-------------|
| Identity | `src/` (koren) | Laravel 10, Sanctum |
| Moderation | `services/moderation-service/` | Laravel 10 |
| Chat | `services/chat-service/` | Node.js, Express, Mongoose |
| Gateway | `api-gateway/` | nginx |
| Frontend | `frontend/` | React 18, Vite |

**API Gateway** (`api-gateway/nginx.conf`) rutira zahteve:

- `/api/register`, `/login`, `/user`, `/korisnik`, `/uloga` → Identity
- `/api/korisnik/{id}/suspend`, `/api/prijava`, `/api/sankcija` → Moderation
- `/api/chat`, `/api/poruka` → Chat

### 2.2 Kako servisi komuniciraju (sinhrono — domaći)

Pre seminara, glavna komunikacija bila je **HTTP/REST**:

- **Moderation → Identity:** validacija tokena (`GET /api/user`), suspenzija (`PATCH /api/internal/korisnik/{id}/suspend`) sa `X-Internal-Api-Key`
- **Chat → Identity:** validacija tokena pri slanju poruka
- **Identity → Chat:** statistike (`GET /api/stats`)

Ključni fajlovi:

- `services/moderation-service/src/Infrastructure/Clients/IdentityClient.php`
- `services/chat-service/src/middleware/auth.js`
- `src/Presentation/Controllers/InternalController.php`

### 2.3 Tok suspenzije korisnika (domaći — sinhroni)

1. Moderator u frontendu klikne „Suspenduj" (`ModeratorPanel.jsx`)
2. `PATCH /api/korisnik/{id}/suspend` → Gateway → Moderation
3. Moderation kreira **prijavu** i **sankciju** u svojoj bazi
4. Moderation poziva Identity interni API i postavlja `suspendovan` datum
5. Prijava na Identity servisu blokira suspendovanog korisnika
6. Chat servis odbija slanje poruka suspendovanom korisniku

### 2.4 Eksterni API-ji

- **DiceBear** — avatari (`frontend/src/components/Avatar.jsx`)
- **Giphy** — GIF pretraga u chatu (`frontend/src/pages/Chatovi.jsx`, potreban `VITE_GIPHY_API_KEY`)

### 2.5 Docker i kontejnerizacija

`docker-compose.yml` podiže:

- sve mikroservise
- API Gateway
- frontend
- PostgreSQL × 2, MongoDB

Svaki servis ima svoj `Dockerfile` (ukupno 5 image-a za domaći).

Pokretanje:

```bash
docker compose up --build
```

### 2.6 GitFlow i GitHub (domaći)

Razvoj je vođen preko **feature grana** i **Pull Request-ova**:

| PR | Feature grana | Šta donosi |
|----|---------------|------------|
| #11 | `feature/identity-service` | Identity servis |
| #12 | `feature/documentation` | `docs/RNAEP.pdf` |
| #13 | `feature/moderation-service` | Moderation servis |
| #14 | `feature/chat-service` | Chat servis |
| #15 | `feature/api-gateway-docker` | Gateway + docker-compose |
| #16 | `feature/frontend-ci-docs` | README, CI, Vite proxy |
| #17 | `develop` → `main` | Release v1.0.0 |

**Backup grana:** `backup/microservices` — čuva kompletan kod pre GitFlow čišćenja.

**Važno:** Tokom podešavanja GitFlow-a bilo je nekoliko pokušaja (reset, rebase, force push). Finalna istorija na GitHub-u je očišćena: svaka feature grana ima **jedan commit**, merge preko PR-a.

### 2.7 Dokumentacija (domaći)

- `README.md` — opis, arhitektura, pokretanje
- `docs/RNAEP.pdf` — projektna dokumentacija iz I domaćeg

---

## 3. Seminarski rad — šta je dodato

Seminarski rad **nadograđuje** mikroservise. Većina ovog koda je u **lokalnom radnom direktorijumu**; deo još nije pushovan na GitHub `main` (v1.0.0 na remote-u je verzija bez Kafka/seminara).

### 3.1 Event-Driven Architecture (Apache Kafka)

**Cilj:** zameniti deo sinhrone HTTP komunikacije asinhronom preko Kafka.

#### Kafka topic-i (5 komada)

| Topic | Ko šalje (Producer) | Ko sluša (Consumer) | Svrha |
|-------|---------------------|---------------------|-------|
| `suspenzija-zahtev` | Moderation | Event Processor | Zahtev za suspenziju |
| `suspenzija-obrada` | Event Processor | Identity | Validiran događaj |
| `suspenzija-primena` | Identity | Chat | Potvrda suspenzije |
| `poruka-kreirana` | Chat | Moderation | Nova poruka → auto-prijava |
| `greska-pri-obradi` | Event Processor, Identity | Moderation | Greška + kompenzacija |

#### Novi servis: Event Processor (hybrid modul)

**Folder:** `services/event-processor/`

Obavezan **hibridni** modul po zahtevu seminara:

1. **Consumer** — prima `suspenzija-zahtev`
2. **Poslovna logika** — proverava da korisnik postoji (HTTP ka Identity internom API-ju)
3. **Producer** — šalje `suspenzija-obrada` ili `greska-pri-obradi`

Ključni fajlovi:

- `services/event-processor/src/index.js` — glavna logika
- `services/event-processor/src/kafka.js` — Kafka klijent, topic konstante

#### Integracija po servisima

**Moderation** (`services/moderation-service/`):

- `src/Infrastructure/Messaging/KafkaProducer.php` — šalje `suspenzija-zahtev`
- `src/Business/Services/ModerationService.php` — umesto direktnog HTTP suspenda, poziva `dispatchSuspendEvent()`
- `src/Infrastructure/Console/Commands/KafkaConsumeCommand.php` — worker: sluša `poruka-kreirana` i `greska-pri-obradi`
- `src/Infrastructure/Messaging/Consumers/MessageCreatedConsumer.php` — auto-prijava ako poruka sadrži zabranjene reči
- `src/Infrastructure/Messaging/Consumers/ProcessingErrorConsumer.php` — **Saga kompenzacija**

**Identity** (`src/`):

- `src/Infrastructure/Messaging/KafkaProducer.php`
- `src/Infrastructure/Messaging/Consumers/SuspendProcessingConsumer.php` — primenjuje suspenziju
- `src/Infrastructure/Console/Commands/KafkaConsumeCommand.php` — worker za `suspenzija-obrada`

**Chat** (`services/chat-service/`):

- `src/messaging/kafka.js` — šalje `poruka-kreirana`, sluša `suspenzija-primena`
- `src/routes/poruka.js` — nakon kreiranja poruke poziva `publishMessageCreated()`

#### Docker worker-i za Kafka

U `docker-compose.yml` dodati:

- `kafka` (Bitnami image)
- `identity-kafka-worker` — `php artisan kafka:consume`
- `moderation-kafka-worker` — `php artisan kafka:consume`
- `event-processor`

PHP servisi koriste **rdkafka** ekstenziju (instalirana u `Dockerfile` i `services/moderation-service/Dockerfile`).

#### Novi tok suspenzije (seminar — asinhroni)

```
1. Moderation kreira prijavu + sankciju u svojoj bazi
2. Moderation → Kafka: suspenzija-zahtev
3. Event Processor validira korisnika
4. Event Processor → Kafka: suspenzija-obrada
5. Identity worker primenjuje suspenziju u PostgreSQL
6. Identity → Kafka: suspenzija-primena
7. Chat kešira suspendovanog korisnika

Greška bilo gde → greska-pri-obradi → Moderation briše sankciju (kompenzacija)
```

Frontend (`ModeratorPanel.jsx`) ažuriran da prikaže poruku „Zahtev poslat (Kafka)" i osveži listu posle 3 sekunde.

### 3.2 Distribuirani paterni

#### Saga

Suspenzija korisnika prelazi više baza (moderation_db + identity_db). Umesto jedne ACID transakcije:

- koraci idu preko Kafka događaja
- pri grešci `ProcessingErrorConsumer` **poništava** sankciju i menja status prijave na `odbijeno`

#### CQRS (Chat servis)

- **Command (upis):** `POST /api/chat/:id/poruka` → MongoDB
- **Query (čitanje):** `GET /api/poruka` → čita iz MongoDB
- Asinhrona sinhronizacija ka Moderation servisu preko `poruka-kreirana`

#### Circuit Breaker

`services/moderation-service/src/Infrastructure/Clients/CircuitBreaker.php`

Omotava HTTP pozive u `IdentityClient.php`. Posle 3 uzastopne greške, prekida pozive ka Identity servisu na 30 sekundi da spreči kaskadno rušenje.

Test: `services/moderation-service/tests/Unit/CircuitBreakerTest.php`

### 3.3 Bezbednost

| Napad | Gde je rešeno | Kako |
|-------|---------------|------|
| **XSS** | `frontend/src/utils/sanitize.js` | `escapeHtml()` pre prikaza teksta poruka u `Chatovi.jsx` |
| **CSRF** | `frontend/src/main.jsx`, `AuthContext.jsx` | Sanctum `X-XSRF-TOKEN`, `withCredentials`, `/sanctum/csrf-cookie` |
| **IDOR** | `src/Presentation/Controllers/KorisnikController.php` | Provera da korisnik menja samo svoj profil (osim admina) |
| **CORS** | `api-gateway/nginx.conf`, `chat-service/src/index.js` | Dozvoljen samo `localhost:3000` |
| **SQL Injection** | Svi Laravel servisi | Eloquent ORM — nema raw SQL upita |

Gateway takođe prosleđuje `/sanctum/` rute ka Identity servisu za CSRF cookie.

### 3.4 Monitoring (Prometheus + Grafana)

**Folder:** `monitoring/`

| Komponenta | Port | Fajl |
|------------|------|------|
| Prometheus | 9090 | `monitoring/prometheus/prometheus.yml` |
| Grafana | 3001 | `monitoring/grafana/` (dashboard + provisioning) |

Metrike:

- Chat: `/metrics` (broj HTTP zahteva)
- Event Processor: `/metrics` (Kafka događaji)
- Identity/Moderation: `/api/health`

Grafana dashboard: **ITEH Mikroservisi** (`monitoring/grafana/dashboards/iteh-dashboard.json`)

### 3.5 CI/CD

Implementirani workflow fajlovi:

- `.github/workflows/ci.yml` — PHPUnit (Identity + Moderation), frontend build, Docker build 6 image-a, integracioni smoke test (`scripts/ci/smoke-test.sh`)
- `.github/workflows/cd.yml` — push svih image-a na GitHub Container Registry (`ghcr.io/<owner>/iteh-<servis>`)

**CI job-ovi:** `identity-tests`, `moderation-tests`, `frontend-build`, `docker-build` (matrix), `integration-smoke`

**CD:** matrix build-and-push za api-gateway, identity, moderation, chat, event-processor, frontend

---

## 4. GitHub projektni menadžment

### 4.1 Issues (zadaci)

Na GitHub-u postoji **18 zadataka** (#2–#26):

**Zatvoreni (Done) — 15 zadataka:**

- #2–#10: domaći (Identity, Moderation, Chat, Gateway, Docker, Frontend, API-ji, PDF, CI)
- #18–#22, #24: seminar (Kafka, Event Processor, Saga, bezbednost, monitoring, Circuit Breaker/CQRS)

**Otvoreni (To Do / In Progress) — 3 zadatka:**

- #23 — CI/CD workflows (ci.yml, cd.yml) — **završeno**
- #25 — Branch protection na `main` i `develop`
- #26 — GitHub Projects Kanban tabla (ručno kreiranje)

### 4.2 Labele i milestone-i

- Labele: `todo`, `in-progress`, `done`, `microservice`, `seminar`, `security`, ...
- Milestone **Domaći zadatak II**
- Milestone **Seminarski rad**

### 4.3 Kanban tabla

GitHub API **ne može automatski** da kreira Project board (token nema `project` scope).

**Ručno (2 minuta):**

1. https://github.com/users/stevanovicm32/projects/new
2. Template: **Board**, naziv: **ITEH Mikroservisi**
3. Dodaj Issues #2–#26
4. Workflow: zatvoren Issue → kolona **Done**

Detalji: `docs/GITHUB_PROJECTS.md`  
Skripta za Issues: `scripts/setup-github-project.sh`

---

## 5. Struktura foldera (kompletna)

```
ITEH/
├── api-gateway/                 # nginx gateway
│   ├── nginx.conf
│   └── Dockerfile
├── services/
│   ├── chat-service/            # Node.js + MongoDB
│   ├── moderation-service/      # Laravel + PostgreSQL
│   └── event-processor/         # Kafka hybrid (seminar)
├── src/                         # Identity Service (Laravel)
│   ├── Infrastructure/Messaging/   # Kafka producer/consumer
│   └── Presentation/               # API kontroleri
├── frontend/                    # React aplikacija
├── monitoring/                  # Prometheus + Grafana (seminar)
│   ├── prometheus/
│   └── grafana/
├── docs/
│   ├── RNAEP.pdf                # I domaći — projektna dokumentacija
│   ├── GITHUB_PROJECTS.md       # Kanban uputstvo
│   └── IZVESTAJ_RADA.md         # ovaj fajl
├── scripts/
│   └── setup-github-project.sh  # sinhronizacija GitHub Issues
├── docker-compose.yml           # svi servisi + Kafka + monitoring
├── Dockerfile                   # Identity image
└── README.md                    # korisničko uputstvo
```

---

## 6. Kako pokrenuti ceo sistem (sa seminarom)

```bash
# Kloniraj repozitorijum
git clone https://github.com/stevanovicm32/chat-app-2022-0035.git
cd chat-app-2022-0035

# Pokreni sve (uključujući Kafka, workere, Prometheus, Grafana)
docker compose up --build
```

| URL | Šta je |
|-----|--------|
| http://localhost:3000 | Frontend |
| http://localhost:8080 | API Gateway |
| http://localhost:9090 | Prometheus |
| http://localhost:3001 | Grafana (admin / admin) |

Seed test korisnika:

```bash
docker compose exec identity-service php artisan db:seed --force
```

---

## 7. Šta je još uvek otvoreno / preporuke

| Stavka | Status | Akcija |
|--------|--------|--------|
| Seminarski kod na GitHub `main` | Nije pushovan | Feature grana → PR → `develop` → `main`, tag `v1.1.0` |
| `ci.yml` / `cd.yml` | Implementirano | Push na GitHub, zatvoriti Issue #23 |
| Branch protection | Nije uključeno | GitHub Settings → Branches → Issue #25 |
| Kanban tabla | Ručno | Issue #26, vidi `GITHUB_PROJECTS.md` |
| `docker compose up` test | Nije verifikovan | Pokrenuti i ispraviti eventualne greške (Kafka, rdkafka) |

---

## 8. Hronologija rada (kratko)

1. **Monolit → mikroservisi** — podela na Identity, Moderation, Chat, Gateway, Docker Compose
2. **GitFlow čišćenje** — feature grane sa po jednim commitom, PR-ovi #11–#17, tag `v1.0.0`
3. **RNAEP.pdf** — dodat u `docs/`, PR #12
4. **Seminarski rad** — Kafka, Event Processor, Saga, bezbednost, monitoring, paterni
5. **GitHub Issues** — 18 zadataka, labele, milestone-i, skripta za sinhronizaciju
6. **Dokumentacija** — README, GITHUB_PROJECTS.md, ovaj izveštaj

---

## 9. Korisni linkovi

- Repozitorijum: https://github.com/stevanovicm32/chat-app-2022-0035
- Issues: https://github.com/stevanovicm32/chat-app-2022-0035/issues
- Release v1.0.0: https://github.com/stevanovicm32/chat-app-2022-0035/releases/tag/v1.0.0
- Novi Project: https://github.com/users/stevanovicm32/projects/new

---

*Dokument generisan kao tehnički izveštaj implementacije. Za pokretanje i API detalje koristi `README.md`; za Kanban tablu `docs/GITHUB_PROJECTS.md`.*
