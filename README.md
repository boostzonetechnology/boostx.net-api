# boostX Backend (Slim 4 + Doctrine)

PHP API for the boostX CMS: auth, blogs, categories, SEO, regions, pricing plans, and uploads.

## Requirements

- PHP **8.2+** (tested with 8.4)
- Composer
- MySQL / MariaDB (Laragon is fine)
- Extensions: `pdo_mysql`, `json`, `mbstring`

## Quick setup

```bash
cd backend-slim

# 1) Install dependencies
composer install

# 2) Environment
copy .env.example .env
# Edit .env → set DB_* and ADMIN_* values

# 3) Create database (optional helper — drops & recreates DB)
php bin/recreate-db.php

# Or create DB manually in MySQL:
# CREATE DATABASE boostx_cms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# 4) Run migrations
php bin/console migrations:migrate --no-interaction

# 5) Seed data
php bin/console app:seed-admin
php bin/console app:seed-seo
php bin/console app:seed-pricing

# 6) Start API server (http://127.0.0.1:8090)
composer start
```

Health check: [http://127.0.0.1:8090/api/health](http://127.0.0.1:8090/api/health)

## `.env` keys

| Key | Example | Notes |
|-----|---------|--------|
| `APP_URL` | `http://127.0.0.1:8090` | Local API base URL |
| `SITE_URL` | `https://boostx.net` | Public site (sitemap / robots / llms) |
| `DB_HOST` / `DB_PORT` / `DB_NAME` / `DB_USER` / `DB_PASS` | MySQL credentials | |
| `CORS_ORIGINS` | `http://127.0.0.1:5173,http://localhost:5173` | Vite frontend origins |
| `ADMIN_EMAIL` / `ADMIN_PASSWORD` | Admin login for `/api/auth/login` | Used by `app:seed-admin` |
| `DEFAULT_REGION_CODE` | `PK` | Used by pricing seed |

## Useful commands

```bash
# Dev server (port 8090)
composer start
# same as: php -S 127.0.0.1:8090 -t public public/router.php

# Migrations
php bin/console migrations:status
php bin/console migrations:migrate
php bin/console migrations:list

# Seeds
php bin/console app:seed-admin
php bin/console app:seed-seo
php bin/console app:seed-pricing

# Reset DB then migrate + seed
php bin/recreate-db.php
php bin/console migrations:migrate --no-interaction
php bin/console app:seed-admin
php bin/console app:seed-seo
php bin/console app:seed-pricing

# Smoke test (login → category → blog)
php bin/smoke-flow.php
```

## Main API routes

| Method | Path | Auth |
|--------|------|------|
| GET | `/api/health` | Public |
| GET | `/api/pricing` | Public |
| GET | `/api/blogs`, `/api/blogs/{slug}` | Public |
| POST | `/api/auth/login` | Public |
| POST | `/api/auth/logout` | Bearer token |
| GET | `/api/auth/me` | Bearer token |
| * | `/api/admin/*` (blogs, categories, seo, regions, plans, uploads) | Bearer token |
| GET | `/robots.txt`, `/sitemap.xml`, `/llms.txt` | Public |

Admin login example:

```bash
curl -X POST http://127.0.0.1:8090/api/auth/login ^
  -H "Content-Type: application/json" ^
  -d "{\"email\":\"admin@boostx.net\",\"password\":\"BoostX@Admin\"}"
```

## Project layout

```
backend-slim/
├── bin/                 # console, recreate-db, smoke-flow
├── bootstrap/           # Slim app bootstrap
├── config/              # routes, DI, migrations, seed configs
├── migrations/          # Doctrine migrations
├── public/              # index.php + router + uploads
└── src/
    ├── Controller/
    ├── Entity/
    ├── Middleware/
    ├── Repository/
    ├── Service/
    └── Support/
```

## Stack

- **Slim 4** — HTTP API
- **PHP-DI** — dependency injection
- **Doctrine ORM + Migrations** — MySQL persistence
- **vlucas/phpdotenv** — env config
