# mb4it/moonshine-pages

MoonShine package for managing pages and navigation menus in Laravel applications.

## Features

- MoonShine resources for:
  - Pages
  - Menu positions
  - Menu items
- Public page route with configurable prefix (`/{slug}` or `/landings/{slug}`)
- Configurable model and controller classes
- Route source support with dynamic route parameters (`route_params` JSON)
- Helper `menu($code)` for menu tree rendering
- Package view fallback for page rendering

## Requirements

- PHP 8.2+
- Laravel 12+
- moonshine/moonshine 4.8+

## Installation

```bash
composer require mb4it/moonshine-pages
```

Publish config, migrations, and views (optional):

```bash
php artisan vendor:publish --tag=moonshine-pages-config
php artisan vendor:publish --tag=moonshine-pages-migrations
php artisan vendor:publish --tag=moonshine-pages-views
```

Or publish all package assets in one command:

```bash
php artisan vendor:publish --provider="MB\MoonShine\MoonshinePagesServiceProvider"
```

Run migrations:

```bash
php artisan migrate
```

## Configuration

Main config file: `config/moonshine-pages.php`

### Important options

- `register_page_route` - register package route automatically
- `route.route_prefix` - route prefix for pages (`''` or `landings`)
- `route.name` - route name used for page URLs
- `models.page`, `models.menu`, `models.menu_position` - model overrides
- `controllers.page_show` - page show controller override
- `moonshine.register_menu_items` - auto-register package menu in MoonShine

### Route prefix example

```php
'route' => [
    'route_prefix' => 'landings',
    'name' => 'page.show',
    'slug_pattern' => '^[A-Za-z0-9-_]+$',
],
```

Resulting path: `/landings/{slug}`.

## Route source parameters

When menu item `source_type = route`, the form:

1. Shows only named GET routes.
2. Detects placeholders in selected route URI (e.g. `{slug}`, `{id}`).
3. Shows dynamic text fields for each placeholder.
4. Saves values into `menus.route_params` (JSON).

Example `route_params` payload:

```json
{
  "slug": "my-article",
  "id": "42"
}
```

URL generation:

- Uses `route($source_value, $route_params)`.
- Falls back to `#` if route cannot be generated.

## Localization

Package localization files:

- `lang/en/moonshine-pages.php`
- `lang/ru/moonshine-pages.php`

All package UI strings are loaded from these translation files.

