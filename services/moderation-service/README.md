# Chat Aplikacija

Web aplikacija za razmenu poruka u realnom vremenu. Backend je napisan u **Laravel (PHP)**, frontend u **React** sa **Vite**.

## Opis aplikacije

- **Registracija i prijava** korisnika (Laravel Sanctum)
- **Lista chatova** – pregled, pretraga, kreiranje novih chatova
- **Poruke** – slanje teksta i GIF-ova (Giphy API), brisanje poruka (moderator/admin)
- **Uloge** – Admin, Moderator, User (suspendovanje korisnika, promena uloga u admin panelu)
- **Profil** – izmena emaila, lozinke i izbor ikonice (20 unapred definisanih avatara, DiceBear API)
- **Admin panel** – upravljanje korisnicima i ulogama
- **Moderator panel** – suspendovanje korisnika

## Tehnologije

| Sloj      | Tehnologije                    |
|----------|---------------------------------|
| Backend  | PHP 8.1+, Laravel 10, Sanctum, SQLite/MySQL |
| Frontend | React 18, Vite 5, React Router, Axios |

## Preduslovi

- **PHP** 8.1 ili noviji (sa ekstenzijama: pdo, mbstring, openssl, tokenizer, xml, ctype, json, bcmath)
- **Composer**
- **Node.js** 18+ i **npm**
- **Baza** – SQLite (podrazumevano) ili MySQL

---

## Pokretanje pomoću Dockera (Docker + Docker Compose)

Aplikacija je dockerizovana. Potrebno je imati instalirane **Docker** i **Docker Compose**.

### Jednostavno pokretanje

U root folderu projekta:

```bash
# Generiši APP_KEY (jednom) – opciono, entrypoint može i sam
php artisan key:generate --show
# Upisi izlaz u .env kao APP_KEY=base64...

# Pokreni sve servise
docker compose up --build
```

- **Frontend:** http://localhost:3000  
- **Backend API:** http://localhost:8000  

Aplikacija: otvori **http://localhost:3000** u browseru. Baza (SQLite) i storage su u Docker volumenima, pa podaci ostaju i posle gašenja kontejnera.

### Šta Docker Compose pokreće

| Servis   | Opis                          | Port |
|----------|--------------------------------|------|
| backend  | Laravel (PHP 8.2), SQLite, migracije pri startu | 8000 |
| frontend | React + Vite dev server, proxy ka backendu      | 3000 |

### Korisne naredbe

```bash
# Build i pokretanje u pozadini
docker compose up -d --build

# Zaustavljanje
docker compose down

# Logovi
docker compose logs -f

# Seed baze (nakon što kontejneri rade)
docker compose exec backend php artisan db:seed --force
```

### Opciono: fajl `.env` u rootu

Ako u rootu postoji `.env` sa bar `APP_KEY=...`, Docker Compose koristi te vrednosti (npr. `APP_KEY=${APP_KEY}` u compose). Ako nemaš `.env`, entrypoint u backend kontejneru kreira minimalni `.env` i može pokrenuti `php artisan key:generate` pri prvom startu.

---

## Instalacija i pokretanje (bez Dockera)

### 1. Kloniranje i backend

```bash
# U root folderu projekta
cd /putanja/do/ITEH

# PHP zavisnosti
composer install

# Kreiraj .env (npr. kopijom .env.example ako postoji) i podesi bazu
php artisan key:generate
```

U `.env` podesi bazu. Za **SQLite** (najjednostavnije):

```env
DB_CONNECTION=sqlite
# DB_DATABASE apsolutna putanja do database.sqlite ili ostavi prazno za database/database.sqlite
```

Ako koristiš **MySQL**:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ime_baze
DB_USERNAME=root
DB_PASSWORD=
```

Kreiraj SQLite fajl ako ne postoji:

```bash
touch database/database.sqlite
```

Pokreni migracije (i custom putanju ako Laravel ne vidi tvoje migracije):

```bash
php artisan migrate --force
# ili za sve migracije iz custom foldera:
php artisan migrate --path=src/Infrastructure/Database/Migrations --force
```

Opciono – seed (uloge + test korisnici):

```bash
php artisan db:seed --force
```

### 2. Frontend

```bash
cd frontend
npm install
```

Opciono – za GIF pretragu u chatu, u `frontend/.env` dodaj Giphy API ključ (besplatan na [developers.giphy.com](https://developers.giphy.com)):

```env
VITE_GIPHY_API_KEY=tvoj_kljuc
```

Ako backend ne radi na portu 8000, npr. 8001:

```env
VITE_BACKEND_URL=http://127.0.0.1:8001
```

### 3. Pokretanje aplikacije

**Terminal 1 – Laravel backend:**

```bash
php artisan serve --port=8000
```

Backend: **http://localhost:8000**

**Terminal 2 – React frontend:**

```bash
cd frontend
npm run dev
```

Frontend: **http://localhost:3000**

Otvoriti u browseru **http://localhost:3000**. Zahteve ka `/api/*` Vite proxy prosleđuje na backend (8000).

---

## Struktura projekta

```
ITEH/
├── frontend/                 # React (Vite) aplikacija
│   ├── src/
│   │   ├── components/       # Avatar, Button, Modal, itd.
│   │   ├── context/         # AuthContext
│   │   └── pages/            # Chatovi, AdminPanel, Register, itd.
│   └── vite.config.js        # proxy /api -> backend
├── src/                      # Laravel (PHP) izvorni kod
│   ├── Business/             # Servisi
│   ├── DataAccess/           # Modeli, repozitorijumi
│   ├── Infrastructure/       # Config, migracije, HTTP kernel
│   └── Presentation/         # Kontroleri, rute, requestovi
├── .env                      # Konfiguracija (ne commitovati)
├── artisan
├── composer.json
├── Dockerfile                # Backend (Laravel) image
├── docker-compose.yml        # Orkestracija backend + frontend
├── docker-entrypoint.sh      # Entrypoint za backend (migracije, .env)
├── .github/workflows/        # CI/CD (ci.yml, cd.yml)
├── phpunit.xml               # PHPUnit konfiguracija
├── tests/                    # PHPUnit testovi
└── README.md                 # Ovaj fajl
```

---

## Test korisnici (nakon `db:seed`)

U zavisnosti od seedera mogu postojati npr.:

- **Admin** – admin@test.com
- **Moderator** – moderator@test.com  
- **User** – user@test.com  

Lozinke su obično u samom seederu (npr. `password` ili slično). Pogledaj `src/Infrastructure/Database/Seeders/KorisnikSeeder.php` za tačne podatke.

---

## Dodatne naredbe

- Migracija samo za kolonu `avatar_seed` (ako je potrebno):
  ```bash
  php artisan migrate --path=src/Infrastructure/Database/Migrations/2024_01_01_000013_add_avatar_seed_to_korisnik_table.php --force
  ```
- Build frontenda za produkciju:
  ```bash
  cd frontend && npm run build
  ```

---

## CI/CD (GitHub Actions)

Projekat koristi **GitHub Actions** za automatsko pokretanje testova, build Docker imageova i (opciono) deployment.

### CI pipeline (`.github/workflows/ci.yml`)

- **Okidač:** svaki `push` i `pull_request` na grane `main` i `develop`.
- **Koraci:**
  1. **Backend testovi** – PHP 8.4, `composer install`, `php artisan test` (PHPUnit).
  2. **Frontend build** – Node 20, `npm ci`, `npm run build`.
  3. **Docker build** – build backend i frontend imageova (bez push-a).

### CD pipeline (`.github/workflows/cd.yml`)

- **Okidač:** `push` na `main` ili ručno (`workflow_dispatch`).
- **Koraci:**
  1. **Build i push** – Docker imagei se build-uju i push-uju u **GitHub Container Registry** (`ghcr.io`):
     - `ghcr.io/<org>/iteh-backend:latest` i `ghcr.io/<org>/iteh-backend:<sha>`
     - `ghcr.io/<org>/iteh-frontend:latest` i `ghcr.io/<org>/iteh-frontend:<sha>`
- **Deployment:** U workflow-u je pripremljen (zakomentarisan) placeholder job za deploy na cloud (npr. Azure Container Apps, AWS ECS, Google Cloud Run). Kada imate okruženje i credentials (npr. u GitHub Secrets ili Environment), odkomentarisati korake i pozvati odgovarajuću CLI (az, aws, gcloud).

### Lokalno pokretanje testova

```bash
composer install
php artisan test
```

### Podešavanje deploya na cloud

1. U GitHub repo: **Settings → Secrets and variables → Actions** dodajte potrebne secrets (npr. `AZURE_CREDENTIALS`, `AWS_ACCESS_KEY_ID`).
2. U `.github/workflows/cd.yml` odkomentarisati job `deploy` i dodati korake za vaš provajder (primeri u komentarima).

---

## Autor / Projekat

Projekat izrađen u okviru predmeta ITEH (informacione tehnologije u elektrotehnici i računarstvu).
