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
            'slug' => 'Slug prefix',
            'prepend_menu_slug' => 'Prepend slug to page URL',
            'image' => 'Image',
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
            'none' => 'No source',
            'link' => 'Link',
            'page' => 'Page',
            'route' => 'Route',
        ],
        'hints' => [
            'link' => 'Full or relative URL',
            'route_parameter' => 'Enter value for route placeholder',
            'image' => 'Optional icon or thumbnail for the menu item',
            'slug' => 'Prefix segment, e.g. "news". The page link becomes /{prefix}/{page-slug}; a page with that exact full slug must exist.',
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
    'menu_manager' => [
        'title' => 'Menu management',
        'copy_suffix' => '(copy)',
        'create_item' => '+ Create item',
        'create_item_footer' => '+ Create menu item',
        'add_existing' => '+ Add from existing',
        'empty' => 'This position has no items yet',
        'nest_hint' => 'Drop here to nest',
        'status' => [
            'active' => 'active',
            'hidden' => 'hidden',
        ],
        'actions' => [
            'edit' => 'edit',
            'duplicate' => 'dup.',
            'hide' => 'hide',
            'show' => 'show',
            'detach' => 'unlink',
            'delete' => 'delete',
        ],
        'confirm' => [
            'detach' => 'Remove this item from the position?',
            'delete' => 'Delete the item completely? Children will be moved one level up.',
        ],
        'modal' => [
            'title' => 'Add from existing',
            'search' => 'Search by name...',
            'available' => 'Available items',
            'already' => 'already in position',
            'empty' => 'Nothing found',
            'cancel' => 'Cancel',
            'add_selected' => 'Add selected',
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
        'hints' => [
            'slug' => 'Multi-segment paths are allowed, e.g. catalog/feature/common.',
        ],
    ],
];
