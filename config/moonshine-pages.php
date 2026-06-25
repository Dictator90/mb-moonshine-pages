<?php

declare(strict_types=1);

use MB\MoonShine\Http\Controllers\PageShowController;
use MB\MoonShine\Models\Menu;
use MB\MoonShine\Models\MenuPosition;
use MB\MoonShine\Models\Page;
use MB\MoonShine\MoonShine\Pages\MenuManagerPage;
use MB\MoonShine\MoonShine\Resources\Menu\MenuResource;
use MB\MoonShine\MoonShine\Resources\MenuPosition\MenuPositionResource;
use MB\MoonShine\MoonShine\Resources\Page\PageResource;

return [
    /*
    |--------------------------------------------------------------------------
    | Register package page route
    |--------------------------------------------------------------------------
    |
    | If true, the package will register its own public page route.
    | If false, the host application can define the route manually.
    |
    */
    'register_page_route' => true,

    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    |
    | Override these classes if you want to use your own Eloquent models.
    | Classes must be compatible with package relations and fields.
    |
    */
    'models' => [
        'page' => Page::class,
        'menu' => Menu::class,
        'menu_position' => MenuPosition::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Database table names
    |--------------------------------------------------------------------------
    |
    | Override when your schema uses custom table names. Must match migrations
    | (publish and edit migrations if you change these on a new project).
    |
    */
    'tables' => [
        'pages' => 'pages',
        'menus' => 'menus',
        'menu_positions' => 'menu_positions',
        'menu_menu_position' => 'menu_menu_position',
    ],

    /*
    |--------------------------------------------------------------------------
    | Controllers
    |--------------------------------------------------------------------------
    |
    | Override package controllers used by routes.
    |
    */
    'controllers' => [
        'page_show' => PageShowController::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | MoonShine resources
    |--------------------------------------------------------------------------
    |
    | Override the MoonShine resource classes registered by this package.
    | Custom classes must extend (or be compatible with) the originals.
    |
    */
    'resources' => [
        'menu' => MenuResource::class,
        'menu_position' => MenuPositionResource::class,
        'page' => PageResource::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | MoonShine pages
    |--------------------------------------------------------------------------
    |
    | Override the standalone MoonShine page classes registered by this package.
    |
    */
    'pages' => [
        'menu_manager' => MenuManagerPage::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Route settings
    |--------------------------------------------------------------------------
    |
    | route_prefix:
    | - ''          => /{slug}
    | - 'landings'  => /landings/{slug}
    |
    */
    'route' => [
        'route_prefix' => '',
        'name' => 'page.show',
        /*
        | Allows multi-segment slugs such as "catalog/feature/common" while
        | forbidding leading, trailing and doubled slashes. Must stay anchored
        | with ^...$ for SlugRouteConstraint to inject the reserved-slug lookahead.
        */
        'slug_pattern' => '^[A-Za-z0-9-_]+(?:/[A-Za-z0-9-_]+)*$',
        /*
        | When true, single-segment slugs that match reserved_slugs (or MoonShine admin
        | prefix when reserved_slugs is null) are not handled by the page route.
        */
        'apply_reserved_slugs' => true,
        /*
        | Exact first-URL-segment values to exclude (e.g. admin panel prefix). Use null
        | to derive the MoonShine panel prefix from config('moonshine.prefix', 'admin').
        */
        'reserved_slugs' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Fields
    |--------------------------------------------------------------------------
    |
    | Swap MoonShine field components used by package resources. The value must
    | be a field class with a ::make(string $label, string $column) signature
    | (a class-string, not a closure, so config caching keeps working).
    |
    | page_content: the page form editor. Defaults to TinyMCE; set it to
    | \MoonShine\CKEditor\Fields\CKEditor::class, \MoonShine\UI\Fields\Textarea::class
    | or any custom field to plug in your own editor.
    |
    */
    'fields' => [
        'page_content' => \MoonShine\TinyMce\Fields\TinyMce::class,
        /*
        | Built-in SEO fields (seo_title / seo_description) on the Page resource.
        | Set to false when the host app manages SEO separately or has dropped
        | those columns from the pages table — when disabled they are removed
        | from the form, detail, index columns, filters and search so the
        | resource works against a schema without the columns.
        */
        'page_seo' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Menu resource
    |--------------------------------------------------------------------------
    |
    | index_tree: render the Menu index as a hierarchical tree (ordered by
    | parent_id, with indentation in the name column) instead of a flat
    | paginated list. Pagination is disabled in tree mode so the whole
    | hierarchy stays visible.
    |
    */
    'menu' => [
        'index_tree' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Menu manager page
    |--------------------------------------------------------------------------
    |
    | enabled: register the standalone "Menu management" admin page (grouped by
    | position, drag & drop within a position) and add it to the MoonShine menu.
    |
    */
    'menu_manager' => [
        'enabled' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | MoonShine menu integration
    |--------------------------------------------------------------------------
    |
    | If true, package resources will be automatically added to MoonShine menu.
    | Keep false when your app builds menu in a custom layout.
    |
    */
    'moonshine' => [
        'register_menu_items' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Media (file uploads)
    |--------------------------------------------------------------------------
    |
    | disk: defaults to the MoonShine panel disk (moonshine.disk).
    | Per-resource upload directories are listed under 'resources'.
    |
    */
    'media' => [
        'disk' => null, // null = inherit moonshine.disk
        'image_dir' => 'menu',
    ],
];
