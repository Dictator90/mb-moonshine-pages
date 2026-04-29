# mb4it/moonshine-pages

MoonShine package for managing pages and navigation menus in Laravel applications.

## Features

- MoonShine resources for Pages, Menu positions, and Menu items
- Public page route with configurable prefix (`/{slug}` or `/prefix/{slug}`)
- Configurable model, resource, and controller classes
- Menu items support three source types: direct link, page, or named route
- Dynamic route parameters — placeholders detected from the route URI and saved as JSON
- Optional image per menu item
- `menu($code)` helper returns a cached nested tree for frontend rendering
- Package view fallback for page rendering
- Reserved-slug protection — prevents the admin prefix from matching the page route
- Localization: `en`, `ru`

## Requirements

- PHP 8.2+
- Laravel 12+
- moonshine/moonshine 4.8+

## Installation

```bash
composer require mb4it/moonshine-pages
```

Run migrations:

```bash
php artisan migrate
```

Publish config, migrations, views, and/or translations as needed:

```bash
php artisan vendor:publish --tag=moonshine-pages-config
php artisan vendor:publish --tag=moonshine-pages-migrations
php artisan vendor:publish --tag=moonshine-pages-views
php artisan vendor:publish --tag=moonshine-pages-lang
```

## Configuration

Main config file: `config/moonshine-pages.php`

### Route

```php
'register_page_route' => true,

'route' => [
    'route_prefix'        => '',           // '' → /{slug}, 'landings' → /landings/{slug}
    'name'                => 'page.show',
    'slug_pattern'        => '^[A-Za-z0-9-_]+$',
    'apply_reserved_slugs' => true,        // exclude admin prefix from slug matches
    'reserved_slugs'      => null,         // null = derive from moonshine.prefix
],
```

### Models

Override Eloquent model classes without subclassing the service provider:

```php
'models' => [
    'page'          => \App\Models\Page::class,
    'menu'          => \App\Models\Menu::class,
    'menu_position' => \App\Models\MenuPosition::class,
],
```

### Resources

Override MoonShine resource classes:

```php
'resources' => [
    'menu'          => \App\MoonShine\Resources\MenuResource::class,
    'menu_position' => \App\MoonShine\Resources\MenuPositionResource::class,
    'page'          => \App\MoonShine\Resources\PageResource::class,
],
```

### Tables

Override database table names (publish and edit migrations before first `migrate`):

```php
'tables' => [
    'pages'              => 'pages',
    'menus'              => 'menus',
    'menu_positions'     => 'menu_positions',
    'menu_menu_position' => 'menu_menu_position',
],
```

### Controllers

```php
'controllers' => [
    'page_show' => \App\Http\Controllers\PageShowController::class,
],
```

### Media

```php
'media' => [
    'disk'      => null,    // null = inherit moonshine.disk
    'image_dir' => 'menu',  // upload directory for menu images
],
```

### MoonShine menu

```php
'moonshine' => [
    'register_menu_items' => true, // set false when building a custom layout
],
```

## Menu source types

Each menu item has a `source_type` that controls URL generation:

| `source_type` | Behaviour |
|---|---|
| `link` | Raw URL stored in `source_value`. |
| `page` | Resolved via the configured page route name; falls back to `#` if the page is inactive. |
| `route` | `route($source_value, $route_params)`; falls back to `#` on failure. |

When `source_type = route` the admin form detects URI placeholders (e.g. `{slug}`, `{id}`) in the selected route and renders a text field for each one. Values are saved in `menus.route_params` (JSON):

```json
{ "slug": "my-article", "id": "42" }
```

## `menu()` helper

Returns a cached nested tree for a given menu position code:

```php
$items = menu('main');
// [
//   ['id' => 1, 'name' => 'Home', 'url' => '/', 'image' => null, 'target' => null, 'code' => 'main', 'children' => []],
//   ...
// ]
```

The tree is cached forever. It is automatically invalidated when a `Menu` model is saved or deleted. If you mutate menu data outside of Eloquent events (bulk updates, raw SQL) call `MenuRepository::forget($code)` manually.

## Localization

UI strings are loaded from the `moonshine-pages` translation namespace:

- `lang/en/moonshine-pages.php`
- `lang/ru/moonshine-pages.php`

Publish to override:

```bash
php artisan vendor:publish --tag=moonshine-pages-lang
```
