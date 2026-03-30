<?php

declare(strict_types=1);

return [
    'menu_group' => [
        'content' => 'Content',
    ],
    'common' => [
        'is_active' => 'Active',
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
        'fields' => [
            'name' => 'Name',
            'code' => 'Code',
            'description' => 'Description',
        ],
        'hints' => [
            'code' => 'For example: main, footer',
        ],
    ],
    'page' => [
        'resource_title' => 'Pages',
        'fields' => [
            'title' => 'Title',
            'slug' => 'Slug',
            'content' => 'Content',
            'seo_title' => 'SEO title',
            'seo_description' => 'SEO description',
        ],
    ],
];
