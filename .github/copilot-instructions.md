Purpose
-------
This file gives AI coding agents the minimal, actionable knowledge to be productive in this Laravel + Inertia + React codebase.

Quick architecture
------------------
- Backend: Laravel (app/*, routes/*) — Laravel v12 and Fortify for auth.
- Frontend: Inertia + React (resources/js/*). Pages live in `resources/js/pages/**/*.tsx` and are resolved by `resources/js/app.tsx`.
- Bundling: Vite (`package.json` scripts). SSR is supported via `npm run build:ssr` + `php artisan inertia:start-ssr`.
- Generated guidance: `laravel/boost` may read/write guideline files (including this file) via `boost:update`/`boost:install`.

Key commands
------------
- Setup (one-liner): run the `composer` `setup` script in `composer.json` or run these manually:
  - `composer install`
  - copy `.env.example` → `.env`
  - `php artisan key:generate`
  - `php artisan migrate`
  - `npm install`
  - `npm run build`
- Development (local): `composer dev` (runs `php artisan serve`, queue listener, and `npm run dev` using `concurrently`).
- SSR dev: `composer dev:ssr` (uses `php artisan pail` and `inertia:start-ssr`).
- Frontend dev: `npm run dev`; build: `npm run build`; build SSR: `npm run build:ssr`.
- Tests: `composer test` (runs `php artisan test`). Tests use an in-memory SQLite database (see `phpunit.xml`).

Important project conventions
-----------------------------
- Inertia pages: Add React pages to `resources/js/pages/Name.tsx`. Routes should return `Inertia::render('Name')` (see [routes/web.php](routes/web.php#L1)).
- Layout: The root Blade template is [resources/views/app.blade.php](resources/views/app.blade.php#L1); it includes Vite assets and the Inertia mount.
- Auth: Fortify is configured to return Inertia views in `app/Providers/FortifyServiceProvider.php` (login/register/reset flows). Custom Fortify actions live under `app/Actions/Fortify/`.
- Rate-limiting & username rules: Login and two-factor rate limiting are defined in `FortifyServiceProvider`; usernames are lowercased by default (`config/fortify.php`).
- State & appearance: Theme initialization happens in `resources/js/hooks/use-appearance` and is invoked from `resources/js/app.tsx`.

Patterns and locations
----------------------
- Controllers: `app/Http/Controllers/`
- Models: `app/Models/` (e.g., `User.php`)
- Migrations & factories: `database/migrations/`, `database/factories/`
- Tests: `tests/` (Pest is installed; `composer test` uses `artisan test`).
- Frontend components: `resources/js/components/` and shared hooks in `resources/js/hooks/`.

Integration notes & gotchas
--------------------------
- Laravel Boost: the project uses `laravel/boost`. Running `php artisan boost:update` may regenerate guideline files (including copilot files). If you edit this file, note that Boost may overwrite it during boost commands.
- Environment: `.env` is required for many commands; `composer setup` copies `.env.example` automatically.
- Database during tests: `phpunit.xml` sets `DB_CONNECTION=sqlite` and `DB_DATABASE=:memory:` — use factories and avoid relying on an external DB in tests.

Example tasks (how to change things)
-----------------------------------
- Add a new Inertia page:
  1. Create `resources/js/pages/Settings/Profile.tsx`.
  2. Add route in `routes/settings.php` or `routes/web.php` that returns `Inertia::render('Settings/Profile')`.
- Modify login view behavior: change the Inertia view mapping in `app/Providers/FortifyServiceProvider.php`.

When in doubt
-------------
- Search for usage in `resources/js/app.tsx`, `app/Providers/FortifyServiceProvider.php`, and `routes/` — these are the canonical integration points between backend and frontend.
- Prefer editing React pages and their corresponding routes rather than changing Blade templates except for global layout concerns in `resources/views/app.blade.php`.

Feedback
--------
If anything here is unclear or you want more examples (e.g., adding API endpoints, SSR notes, or test examples), say which area and I'll expand this file.
