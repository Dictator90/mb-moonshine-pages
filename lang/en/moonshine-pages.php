<?php

declare(strict_types=1);

return [
    'menu_group' => [
        'content' => 'Content',
    ],
    'common' => [
        'is_active' => 'Active',
        'yes' => 'Yes',
        'no' => 'No',
        'sort_order' => 'Sort order',
        'created_at' => 'Created at',
        'updated_at' => 'Updated at',
        'tabs' => [
            'main' => 'Main',
            'seo' => 'SEO',
        ],
    ],
    'menu' => [
        'resource_title' => 'Menu',
        'fields' => [
            'name' => 'Name',
            'positions' => 'Positions',
            'source_type' => 'Source',
            'link' => 'Link',
            'page' => 'Page',
            'route' => 'Route',
            'route_parameter' => 'Route parameter: :parameter',
            'route_params' => 'Route parameters',
            'source_value' => 'Link or route name',
            'parent' => 'Parent',
        ],
        'source_types' => [
            'link' => 'Link',
            'page' => 'Page',
            'route' => 'Route',
        ],
        'hints' => [
            'link' => 'Full or relative URL',
            'route_parameter' => 'Enter value for route placeholder',
        ],
    ],
    'menu_position' => [
        'resource_title' => 'Menu positions',
        'tabs' => [
            'menus' => 'Menus',
        ],
        'fields' => [
            'name' => 'Name',
            'code' => 'Code',
            'description' => 'Description',
            'menus_relation' => 'Menu items',
        ],
        'hints' => [
            'code' => 'For example: main, footer',
        ],
    ],
    'page' => [
        'resource_title' => 'Pages',
        'actions' => [
            'open_public' => 'Open on site',
        ],
        'fields' => [
            'title' => 'Title',
            'slug' => 'Slug',
            'content' => 'Content',
            'seo_title' => 'SEO title',
            'seo_description' => 'SEO description',
        ],
    ],
];
