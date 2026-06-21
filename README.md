# Chat Aplikacija — Mikroservisna arhitektura

Web aplikacija za razmenu poruka. Sistem je podeljen na **tri mikroservisa** sa **API Gateway**-jem kao jedinstvenom ulaznom tačkom, u skladu sa **Database per service** patternom.

## Arhitektura

```
Browser → Frontend (React :3000)
              ↓
         API Gateway (nginx :8080)
         ├── Identity Service (:8001)  → PostgreSQL (korisnik, uloga)
         ├── Moderation Service (:8002) → PostgreSQL (prijava, sankcija)
         └── Chat Service (:8003)      → MongoDB (konverzacije, poruke)
```

| Servis | Tehnologija | Baza | Odgovornost |
|--------|-------------|------|-------------|
| **API Gateway** | nginx | — | Rutiranje, jedinstvena ulazna tačka |
| **Identity Service** | Laravel 10, Sanctum | PostgreSQL | Korisnici, uloge, autentifikacija |
| **Moderation Service** | Laravel 10 | PostgreSQL | Prijave, sankcije, suspenzija |
| **Chat Service** | Node.js, Express, Mongoose | MongoDB | Konverzacije i poruke |
| **Frontend** | React 18, Vite | — | Korisnički interfejs |

### Eksterni API-ji (open-source)

- **DiceBear** — generisanje avatara (`api.dicebear.com`)
- **Giphy** — GIF pretraga u chatu (`api.giphy.com`)

## Pokretanje (Docker Compose)

Preduslov: **Docker** i **Docker Compose**.

```bash
docker compose up --build
```

| URL | Opis |
|-----|------|
| http://localhost:3000 | Frontend (React) |
| http://localhost:8080 | API Gateway (jedina ulazna tačka za API) |
| http://localhost:8001 | Identity Service (direktno, dev) |
| http://localhost:8002 | Moderation Service (direktno, dev) |
| http://localhost:8003 | Chat Service (direktno, dev) |

### Korisne naredbe

```bash
docker compose up -d --build    # Pokretanje u pozadini
docker compose down             # Zaustavljanje
docker compose logs -f          # Logovi
docker compose ps               # Status servisa
```

### Seed test korisnika (Identity)

```bash
docker compose exec identity-service php artisan db:seed --force
```

## Struktura projekta

```
ITEH/
├── api-gateway/              # nginx API Gateway
├── services/
│   ├── chat-service/         # Node.js + MongoDB
│   └── moderation-service/   # Laravel + PostgreSQL
├── src/                      # Identity Service (Laravel)
├── frontend/                 # React aplikacija
├── docker-compose.yml        # Orkestracija svih servisa
└── Dockerfile                # Identity Service image
```

## API rutiranje (Gateway)

| Putanja | Servis |
|---------|--------|
| `/api/register`, `/api/login`, `/api/user`, `/api/korisnik`, `/api/uloga` | Identity |
| `/api/korisnik/{id}/suspend`, `/api/prijava`, `/api/sankcija` | Moderation |
| `/api/chat`, `/api/poruka` | Chat |

## Lokalni razvoj (bez Dockera)

Zahteva: PHP 8.4, Composer, Node 20, PostgreSQL × 2, MongoDB.

1. Pokreni baze i servise pojedinačno
2. Identity: `DB_CONNECTION=pgsql`, port 8001
3. Moderation: u `services/moderation-service`, port 8002
4. Chat: u `services/chat-service`, `npm start`, port 8003
5. Gateway: nginx sa `api-gateway/nginx.conf`
6. Frontend: `cd frontend && npm run dev` (proxy → `:8080`)

## CI/CD

GitHub Actions (`.github/workflows/ci.yml`) build-uje sve Docker imageove: gateway, identity, moderation, chat i frontend.

---

Projekat izrađen u okviru predmeta ITEH.
