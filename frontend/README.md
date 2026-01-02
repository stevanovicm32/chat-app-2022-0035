# Chat Aplikacija - Frontend

React aplikacija za chat sistem.

## Instalacija

```bash
npm install
```

## Pokretanje

```bash
npm run dev
```

Aplikacija će biti dostupna na `http://localhost:3000`

## Struktura

- **Login** - Stranica za prijavu korisnika
- **Chatovi** - Stranica sa listom chatova
- **Admin Panel** - Administratorski panel (samo za Admin ulogu)

## API Povezivanje

Frontend se povezuje sa Laravel backend-om preko proxy konfiguracije u `vite.config.js`.
Backend treba da radi na `http://localhost:8000`.

