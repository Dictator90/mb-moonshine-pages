# AGENTS.md

## Cursor Cloud specific instructions

This is a PHP/Laravel **library package** (`mb4it/moonshine-pages`) — it cannot run standalone. To develop and test, you must create a host Laravel application that requires this package as a local path dependency.

### System dependencies

- **PHP 8.2+** with extensions: `mbstring`, `xml`, `curl`, `zip`, `sqlite3`, `intl`, `bcmath`, `gd`, `dom`
- **Composer** (globally installed)
- **SQLite** (comes pre-installed on most systems)

### Testing the package locally

Since this is a Composer library with no test suite, no linting config, and no standalone entry point:

1. **Create a host Laravel app** in `/tmp/test-app`:
   ```bash
   cd /tmp && composer create-project laravel/laravel test-app
   ```
2. **Add the local package** as a path repository and install it along with MoonShine:
   ```bash
   cd /tmp/test-app
   composer config repositories.local path /workspace
   composer require moonshine/moonshine:^4.8 --no-interaction
   composer require "mb4it/moonshine-pages:*" --no-interaction
   ```
   The package is **symlinked** from `/workspace`, so code changes are reflected instantly.
3. **Run MoonShine install** (non-interactive — it will fail on the admin user prompt but migrations run fine):
   ```bash
   php artisan moonshine:install --no-interaction
   ```
4. **Create an admin user manually**:
   ```bash
   php artisan moonshine:user --name=admin --username=admin@example.com --password=password --no-interaction
   ```
5. **Publish package assets and run migrations**:
   ```bash
   php artisan vendor:publish --provider="MB\MoonShine\MoonshinePagesServiceProvider" --no-interaction
   php artisan migrate --no-interaction
   ```
6. **Enable menu registration** by setting `register_menu_items => true` in `config/moonshine-pages.php`.
7. **Start the dev server**: `php artisan serve --host=0.0.0.0 --port=8000`

### Key routes

- MoonShine admin: `http://localhost:8000/admin` (dashboard at `/admin/page/dashboard`)
- Public pages: `http://localhost:8000/{slug}` (when `register_page_route` is `true`)

### Gotchas

- `php artisan moonshine:install` fails on the admin-user creation prompt in non-interactive mode. Use `moonshine:user` separately.
- MoonShine v4 dashboard route is `/admin/page/dashboard`, not just `/admin`.
- The `is_active` boolean on the Page model must be `true` for a page to render publicly.
- PHP syntax checking: `find src -name '*.php' -exec php -l {} \;`
- No automated tests (`phpunit.xml`, `tests/` directory) or linting tools exist in this repo.
