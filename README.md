# Laravel ERP (Backend + Inertia/React)

Lightweight README to get started with development, testing and common tasks.

## Quick overview
- Backend: Laravel v12 (app/*), REST API with Sanctum tokens.
- Frontend: Inertia + React (resources/js/*), bundled with Vite.
- Dev environment: Laravel Sail (Docker) providing PostgreSQL, Redis and Mailpit.
- Tests: Pest / PHPUnit feature tests located under `tests/Feature/`.

## Prerequisites
- Docker (desktop) and Docker Compose
- PHP, Composer, Node/npm (only needed if running locally without Sail)

## Start development (recommended: Sail)
1. Start containers:

```bash
docker compose up -d
```

2. Run artisan commands inside the container:

```bash
docker compose exec -T laravel.test php artisan migrate --force
docker compose exec -T laravel.test php artisan db:seed --force
```

3. Start frontend dev server (HMR) — either inside container or locally:

```bash
# inside container
docker compose exec -T laravel.test bash -lc "npm run dev"

# or locally
npm install
npm run dev
```

Frontend HMR (Vite) is available on port `5173` and backend on port `80`.

## Useful commands
- Artisan (inside sail): `docker compose exec -T laravel.test php artisan <command>`
- Run tests:

```bash
docker compose exec -T laravel.test php artisan test
```

- View logs:

```bash
docker compose logs -f laravel.test
```

- Enter shell:

```bash
docker compose exec -T laravel.test bash
```

## Environment
- Primary environment file: `.env` — this project uses PostgreSQL in Docker:
  - `DB_HOST=postgres`
  - `DB_DATABASE=laravel_erp`
  - `DB_USERNAME=laravel`
  - `DB_PASSWORD=secret`

- Testing environment: `.env.testing` exists and is used by tests; by default tests run against the same DB in this setup (development DB). See `phpunit.xml` and `.env.testing`.

## Authentication API (quick reference)
- POST `/api/login` — body: `{ email, password }` → returns token
- POST `/api/logout` — requires `Authorization: Bearer <token>` → revokes token
- GET `/api/me` — requires token → returns authenticated user

Middleware order for protected routes (important):
1. `auth:sanctum`
2. `token.not.revoked` (custom, checks revoked tokens)
3. `auth.active` (custom, checks `is_active` on `User`)

See `routes/api.php` for concrete usage.

## Tests & Patterns
- Tests live in `tests/Feature/` (Pest compatible file patterns exist)
- Authentication tests: `tests/Feature/Auth/` (LoginTest, LogoutTest, MeTest)
- Use model factories (`database/factories/`) and `RefreshDatabase` semantics where used.
- For API tests, use `$this->postJson()` / `$this->getJson()` and pass token header: `['Authorization' => 'Bearer ' . $token]`.

## Conventions & guidelines (project-specific)
- Use `casts()` method on models instead of `$casts` property where present in the codebase.
- Register custom middleware aliases in `bootstrap/app.php`.
- When adding API endpoints:
  - Create controller under `app/Http/Controllers/Api/`
  - Add route in `routes/api.php` and apply middleware group
  - Add feature tests under `tests/Feature/Api/` and run them via `php artisan test` in Sail

## Troubleshooting
- Vite manifest error: run `npm run build` or start `npm run dev`.
- If Docker containers fail to start, check `compose.yaml` (Sail) and `docker compose logs` for the `laravel.test` service.
- If tests depend on database connectivity, ensure `postgres` container is healthy and `DB_HOST` is `postgres`.

## Where to look
- Auth controller & middleware: `app/Http/Controllers/Api/AuthController.php`, `app/Http/Middleware/`
- Routes: `routes/api.php`, `routes/web.php`
- Frontend entry: `resources/js/app.tsx`, pages under `resources/js/pages/`
- Tests: `tests/Feature/Auth/`

---
If you want a longer README (deployment, CI, environment matrix, or contributor guide), tell me which sections to expand and I will add them.
