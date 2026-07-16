<?php

declare(strict_types=1);

namespace MB\MoonShine\Support;

use MB\MoonShine\MoonShine\Resources\Menu\MenuResource;
use MB\MoonShine\MoonShine\Resources\MenuPosition\MenuPositionResource;
use MB\MoonShine\MoonShine\Resources\Page\PageResource;

/**
 * Resolves MoonShine resource classes registered by the package.
 *
 * The host application may override any of them via `moonshine-pages.resources.*`;
 * package pages and relation fields must reference the configured class instead of
 * the vendor one, otherwise MoonShine fails with "Resource is required" because the
 * vendor class is not registered in the core.
 */
final class MoonShinePagesResources
{
    /**
     * @return class-string
     */
    public static function page(): string
    {
        return (string) config('moonshine-pages.resources.page', PageResource::class);
    }

    /**
     * @return class-string
     */
    public static function menu(): string
    {
        return (string) config('moonshine-pages.resources.menu', MenuResource::class);
    }

    /**
     * @return class-string
     */
    public static function menuPosition(): string
    {
        return (string) config('moonshine-pages.resources.menu_position', MenuPositionResource::class);
    }
}
