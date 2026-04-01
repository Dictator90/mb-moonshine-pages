<?php

declare(strict_types=1);

namespace MB\MoonShine\Support;

final class MoonShinePagesTables
{
    public static function pages(): string
    {
        return (string) config('moonshine-pages.tables.pages', 'pages');
    }

    public static function menus(): string
    {
        return (string) config('moonshine-pages.tables.menus', 'menus');
    }

    public static function menuPositions(): string
    {
        return (string) config('moonshine-pages.tables.menu_positions', 'menu_positions');
    }

    public static function menuMenuPosition(): string
    {
        return (string) config('moonshine-pages.tables.menu_menu_position', 'menu_menu_position');
    }
}
