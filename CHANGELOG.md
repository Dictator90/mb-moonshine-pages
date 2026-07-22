# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.2] - 2026-07-22

### Fixed
- `MoonshinePagesServiceProvider::boot()` no longer references the removed local variables (`$menuResourceClass`, `$menuPositionResourceClass`, `$pageResourceClass`) in the menu-items block — a leftover from the 1.1.1 resource-resolver refactor that raised `Undefined variable` whenever `register_menu_items` was enabled, breaking any app boot (e.g. `php artisan wayfinder:generate`). Menu items now resolve through `MoonShinePagesResources::menu()` / `::menuPosition()` / `::page()`.

## [1.0.8] - 2026-05-21

### Fixed
- `PageFormPage` slug `unique` validation on create: do not pass an empty primary key to `ignore()` (fixes PostgreSQL `invalid input syntax for type bigint: ""`).

## [1.0.7] - 2026-05-20

### Fixed
- `MenuFormPage` add `asyncOnInit` in position Field

## [1.0.6] - 2026-05-20

### Fixed
- `MenuFormPage` now uses async `BelongsToMany` search for menu positions so the positions list is loaded through the relation endpoint and can be selected reliably by name/code.
- `MenuPositionFormPage` now uses async `BelongsToMany` select mode without child resource fields for menus, so the tab no longer submits nested menu fields that could overwrite the position name with `0`.
- Remove `final` from Resources Pages.

### Removed
- migration `0001_01_01_000003_add_image_to_menus_table.php`

## [1.0.4] - 2026-04-29

### Added
- `image` column on the `menus` table (new migration `0001_01_01_000003_add_image_to_menus_table.php`).
- `Image` field in `MenuResource` (index, detail) and `MenuFormPage` (with `removable()` and a hint).
- `image` key exposed in the `MenuRepository` tree cache so `menu($code)` consumers can render thumbnails.
- `config('moonshine-pages.media.disk')` — defaults to `null`, inheriting `moonshine.disk` at runtime.
- `config('moonshine-pages.media.image_dir')` — upload directory for menu images (default `menu`).
- `config('moonshine-pages.resources.menu/menu_position/page')` — override the MoonShine resource classes registered by this package without publishing the service provider.
- `ServiceProvider` now reads resource classes from `config('moonshine-pages.resources.*')` for both `$core->resources()` and `MenuGroup` menu-item registration.
- `moonshine-pages-lang` publish tag — copies `lang/` to `lang/vendor/moonshine-pages`.

## [1.0.3] - 2026-04-19

### Added
- Full CRUD pages for all three resources: `PageIndexPage`, `PageFormPage`, `PageDetailPage`, `MenuIndexPage`, `MenuFormPage`, `MenuPositionIndexPage`, `MenuPositionFormPage`.
- `MenuFormPage`: dynamic `route_params` fields — detects URI placeholders from the selected named route and renders a `Text` field per placeholder; values saved as JSON.
- `SlugRouteConstraint` — builds the slug regex with an optional negative lookahead that excludes reserved segments (e.g. the MoonShine admin prefix) from matching the public page route.
- `PublicPageUrl` helper — canonical way to build a page URL from a slug, respecting the configured route name.
- `MoonShinePagesTables` — central helper that reads table names from `config('moonshine-pages.tables.*')` so all models and migrations stay in sync.
- `PagePublicActionButton` — "Open on site" action button on the Page resource.
- `MoonshinePagesDemoSeeder` — seeds sample positions, menu items, and pages for development.
- PHPUnit test suite (11 tests): `SlugRouteConstraintTest`, `PublicPageUrlTest`, `MoonshinePagesAdminRouteTest`, `MenuPositionMenusRelationTest`, `MoonshinePagesDemoSeederTest`.
- Localization files for `en` and `ru`.
- Expanded config: `models.*`, `tables.*`, `controllers.*`, `route.*` (prefix, name, slug pattern, reserved slugs).
- `apply_reserved_slugs` / `reserved_slugs` route config options.

### Changed
- Migrations rewritten to use `MoonShinePagesTables` helpers and guard against re-running on existing tables.
- `Menu::getUrlAttribute()` handles `page`, `route`, and `link` source types with fallback to `#`; `route_params` cast to array and normalised (drops empty/non-scalar values).
- `MenuRepository::buildTree()` orders items by `parent_id`, `sort_order`, `name` and returns a nested array including `id`, `name`, `url`, `code`, `target`, `children`.
- `ServiceProvider::boot()` defers route loading to `app->booted()` so host-app config overrides are applied before route registration.
- `composer.json`: added `orchestra/testbench` and `phpunit/phpunit` dev dependencies; added `test` script.

## [1.0.2] - 2026-03-30

_Tag alias for 1.0.0; no code changes._

## [1.0.0] - 2026-03-30

### Added
- `Page`, `Menu`, `MenuPosition` Eloquent models with configurable table names.
- Basic `PageResource`, `MenuResource`, `MenuPositionResource` MoonShine resources.
- Public page route (`/{slug}` or `/{prefix}/{slug}`) with configurable prefix and route name.
- `PageShowController` — looks up an active page by slug; falls back to `moonshine-pages::pages.show` view when the host app has no `pages.show` view.
- `menu(string $code)` global helper backed by `MenuRepository::getTree()` with forever-cache and model-event invalidation.
- `MoonshinePagesServiceProvider` — merges config, registers `MenuRepository` singleton, loads translations/views/migrations, registers resources and optional `MenuGroup`.
- `config/moonshine-pages.php` with `register_page_route` and `moonshine.register_menu_items` toggles.

### Fixed
- Route file renamed from `routes/web.php` to `routes/moonshine-pages.php`.
