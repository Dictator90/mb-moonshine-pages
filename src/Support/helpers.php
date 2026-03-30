<?php

declare(strict_types=1);

use MB\MoonShine\Services\Menu\MenuRepository;

if (! function_exists('menu')) {
    /**
     * @return array<int, array<string, mixed>>
     */
    function menu(string $code): array
    {
        /** @var MenuRepository $repository */
        $repository = app(MenuRepository::class);

        return $repository->getTree($code);
    }
}
