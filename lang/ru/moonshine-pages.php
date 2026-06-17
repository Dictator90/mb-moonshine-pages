<?php

declare(strict_types=1);

return [
    'menu_group' => [
        'content' => 'Контент',
    ],
    'common' => [
        'is_active' => 'Активен',
        'yes' => 'Да',
        'no' => 'Нет',
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
            'slug' => 'Префикс slug',
            'prepend_menu_slug' => 'Добавлять префикс к URL страницы',
            'image' => 'Изображение',
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
            'none' => 'Без источника',
            'link' => 'Ссылка',
            'page' => 'Страница',
            'route' => 'Маршрут',
        ],
        'hints' => [
            'link' => 'Полный или относительный URL',
            'route_parameter' => 'Введите значение для placeholder маршрута',
            'image' => 'Необязательная иконка или миниатюра для пункта меню',
            'slug' => 'Сегмент-префикс, например "news". Ссылка станет /{префикс}/{slug-страницы}; должна существовать страница с таким полным slug.',
        ],
    ],
    'menu_position' => [
        'resource_title' => 'Позиции меню',
        'tabs' => [
            'menus' => 'Меню',
        ],
        'fields' => [
            'name' => 'Название',
            'code' => 'Код',
            'description' => 'Описание',
            'menus_relation' => 'Пункты меню',
        ],
        'hints' => [
            'code' => 'Например: main, footer',
        ],
    ],
    'menu_manager' => [
        'title' => 'Управление меню',
        'copy_suffix' => '(копия)',
        'create_item' => '+ Создать пункт',
        'create_item_footer' => '+ Создать пункт меню',
        'add_existing' => '+ Добавить из существующих',
        'empty' => 'В этой позиции пока нет пунктов',
        'nest_hint' => 'Перетащите сюда, чтобы вложить',
        'status' => [
            'active' => 'активен',
            'hidden' => 'скрыт',
        ],
        'actions' => [
            'edit' => 'ред.',
            'duplicate' => 'дубл.',
            'hide' => 'скр.',
            'show' => 'показать',
            'detach' => 'из поз.',
            'delete' => 'удал.',
        ],
        'confirm' => [
            'detach' => 'Убрать пункт из этой позиции?',
            'delete' => 'Удалить пункт полностью? Потомки будут перемещены на уровень выше.',
        ],
        'modal' => [
            'title' => 'Добавить из существующих',
            'search' => 'Поиск по названию...',
            'available' => 'Доступные пункты',
            'already' => 'уже в позиции',
            'empty' => 'Ничего не найдено',
            'cancel' => 'Отмена',
            'add_selected' => 'Добавить выбранные',
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
        'hints' => [
            'slug' => 'Допустимы многосегментные пути, например catalog/feature/common.',
        ],
    ],
];
