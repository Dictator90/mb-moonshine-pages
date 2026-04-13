<?php

declare(strict_types=1);

return [
    'menu_group' => [
        'content' => 'Контент',
    ],
    'common' => [
        'is_active' => 'Активен',
        'sort_order' => 'Порядок',
        'created_at' => 'Создано',
        'updated_at' => 'Обновлено',
        'tabs' => [
            'main' => 'Основное',
            'seo' => 'SEO',
        ],
    ],
    'menu' => [
        'resource_title' => 'Меню',
        'fields' => [
            'name' => 'Название',
            'positions' => 'Позиции',
            'source_type' => 'Источник',
            'link' => 'Ссылка',
            'page' => 'Страница',
            'route' => 'Маршрут',
            'route_parameter' => 'Параметр маршрута: :parameter',
            'route_params' => 'Параметры маршрута',
            'source_value' => 'Ссылка или имя маршрута',
            'parent' => 'Родитель',
        ],
        'source_types' => [
            'link' => 'Ссылка',
            'page' => 'Страница',
            'route' => 'Маршрут',
        ],
        'hints' => [
            'link' => 'Полный или относительный URL',
            'route_parameter' => 'Введите значение для placeholder маршрута',
        ],
    ],
    'menu_position' => [
        'resource_title' => 'Позиции меню',
        'fields' => [
            'name' => 'Название',
            'code' => 'Код',
            'description' => 'Описание',
        ],
        'hints' => [
            'code' => 'Например: main, footer',
        ],
    ],
    'page' => [
        'resource_title' => 'Страницы',
        'actions' => [
            'open_public' => 'Открыть на сайте',
        ],
        'fields' => [
            'title' => 'Название',
            'slug' => 'Slug',
            'content' => 'Контент',
            'seo_title' => 'SEO title',
            'seo_description' => 'SEO description',
        ],
    ],
];
