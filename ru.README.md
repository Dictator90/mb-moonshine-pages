# mb4it/moonshine-pages

Пакет MoonShine для управления страницами и меню в Laravel-приложении.

## Возможности

- Ресурсы MoonShine для:
  - Страниц
  - Позиций меню
  - Пунктов меню
- Публичный маршрут страницы с настраиваемым префиксом (`/{slug}` или `/landings/{slug}`)
- Настраиваемые классы моделей и контроллера
- Поддержка `source_type = route` с динамическими параметрами маршрута (`route_params` JSON)
- Хелпер `menu($code)` для вывода дерева меню
- Fallback на package view для отображения страниц

## Требования

- PHP 8.2+
- Laravel 12+
- moonshine/moonshine 4.8+

## Установка

```bash
composer require mb4it/moonshine-pages
```

Публикация конфига, миграций и view (опционально):

```bash
php artisan vendor:publish --tag=moonshine-pages-config
php artisan vendor:publish --tag=moonshine-pages-migrations
php artisan vendor:publish --tag=moonshine-pages-views
```

Или можно опубликовать все ресурсы пакета одной командой:

```bash
php artisan vendor:publish --provider="MB\MoonShine\MoonshinePagesServiceProvider"
```

Запуск миграций:

```bash
php artisan migrate
```

## Конфигурация

Основной конфиг: `config/moonshine-pages.php`

### Ключевые опции

- `register_page_route` - автоматически регистрировать маршрут пакета
- `route.route_prefix` - префикс маршрута страниц (`''` или `landings`)
- `route.name` - имя маршрута для генерации URL страниц
- `models.page`, `models.menu`, `models.menu_position` - переопределение моделей
- `controllers.page_show` - переопределение контроллера страницы
- `moonshine.register_menu_items` - авто-добавление пунктов пакета в меню MoonShine

### Пример route_prefix

```php
'route' => [
    'route_prefix' => 'landings',
    'name' => 'page.show',
    'slug_pattern' => '^[A-Za-z0-9-_]+$',
],
```

Итоговый путь: `/landings/{slug}`.

## Параметры маршрутов для меню

Когда у пункта меню `source_type = route`, форма:

1. Показывает только именованные GET-маршруты.
2. Определяет placeholder-параметры из URI выбранного маршрута (например `{slug}`, `{id}`).
3. Показывает динамические текстовые поля для каждого параметра.
4. Сохраняет значения в `menus.route_params` (JSON).

Пример `route_params`:

```json
{
  "slug": "my-article",
  "id": "42"
}
```

Генерация URL:

- Используется `route($source_value, $route_params)`.
- Если URL сгенерировать нельзя, возвращается `#`.

## Локализация

Файлы локализации пакета:

- `lang/en/moonshine-pages.php`
- `lang/ru/moonshine-pages.php`

Все пользовательские строки пакета берутся из этих файлов.

