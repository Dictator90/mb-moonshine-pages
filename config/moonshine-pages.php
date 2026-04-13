<?php

declare(strict_types=1);

use MB\MoonShine\Http\Controllers\PageShowController;
use MB\MoonShine\Models\Menu;
use MB\MoonShine\Models\MenuPosition;
use MB\MoonShine\Models\Page;

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
        'slug_pattern' => '^[A-Za-z0-9-_]+$',
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
];
