# GitHub Projects — Kanban tabla

Projektni panel za praćenje razvoja mikroservisne aplikacije.

## Brzo kreiranje table (2 minuta)

1. Otvori: [Novi GitHub Project](https://github.com/users/stevanovicm32/projects/new)
2. Izaberi šablon **Board**
3. Naziv: **ITEH Mikroservisi**
4. Klikni **Create project**
5. Klikni **+ Add item** → **Add item from repository**
6. Izaberi repozitorijum: `stevanovicm32/chat-app-2022-0035`
7. Dodaj sve Issues (#2–#20)

## Kolone (Kanban)

| Kolona | Značenje |
|--------|----------|
| **To Do** | Zadatak planiran, nije započet |
| **In Progress** | Aktivni razvoj na feature grani |
| **Done** | Završeno i mergovano / zatvoreno |

### Automatizacija statusa

U Project settings → **Workflows** uključi:

- **Item closed** → status **Done**
- (opciono) **Item reopened** → status **To Do**

## Issues po fazama

### Domaći zadatak II (zatvoreni — Done)

| # | Zadatak |
|---|---------|
| #2 | Identity Service (PostgreSQL) |
| #3 | Moderation Service (Prijava/Sankcija) |
| #4 | Chat Service (MongoDB) |
| #5 | API Gateway (nginx) |
| #6 | Docker Compose orkestracija |
| #7 | Frontend integracija |
| #8 | Eksterni API-ji (DiceBear, Giphy) |
| #9 | Projektna dokumentacija (PDF) |
| #10 | CI/CD pipeline |

### Seminarski rad

| Zadatak | Status |
|---------|--------|
| Kafka EDA — 5 topic-a | Done |
| Event Processor (hybrid) | Done |
| Saga patern | Done |
| Bezbednost (XSS, CSRF, IDOR, CORS, SQL) | Done |
| Prometheus + Grafana | Done |
| Circuit Breaker + CQRS | Done |
| CI/CD workflows (ci.yml, cd.yml) | Done |
| Branch protection | To Do |

## Labele

| Label | Boja | Značenje |
|-------|------|----------|
| `todo` | siva | Čeka |
| `in-progress` | žuta | U razvoju |
| `done` | zelena | Završeno |
| `microservice` | plava | Mikroservis |
| `seminar` | crvena | Seminarski rad |
| `security` | crvena | Bezbednost |

## Automatsko podešavanje Issues

```bash
chmod +x scripts/setup-github-project.sh
./scripts/setup-github-project.sh
```

Skripta:
- kreira labele i milestone-e
- zatvara završene zadatke sa komentarom
- kreira Issues za seminarski rad

> **Napomena:** Kreiranje same Project table zahteva `project` scope na GitHub tokenu. Tabla se kreira ručno jednom (koraci iznad); Issues se mogu automatski sinhronizovati.

## Milestone-i

- **Domaći zadatak II** — mikroservisna arhitektura
- **Seminarski rad** — Kafka, bezbednost, monitoring

## Linkovi

- [Repozitorijum](https://github.com/stevanovicm32/chat-app-2022-0035)
- [Issues](https://github.com/stevanovicm32/chat-app-2022-0035/issues)
- [Projects](https://github.com/stevanovicm32/chat-app-2022-0035/projects)
