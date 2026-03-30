<?php

declare(strict_types=1);

namespace MB\MoonShine\Services\Menu;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use MB\MoonShine\Models\Menu;
use MB\MoonShine\Models\MenuPosition;

class MenuRepository
{
    public function __construct(
        private CacheRepository $cache,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getTree(string $code): array
    {
        $key = "menu.tree.{$code}";

        /** @var array<int, array<string, mixed>> $tree */
        $tree = $this->cache->rememberForever($key, function () use ($code): array {
            return $this->buildTree($code);
        });

        return $tree;
    }

    public function forget(?string $code = null): void
    {
        if ($code !== null) {
            $this->cache->forget("menu.tree.{$code}");

            return;
        }

        /** @var class-string<Model> $menuPositionModel */
        $menuPositionModel = (string) config('moonshine-pages.models.menu_position', MenuPosition::class);

        /** @var Collection<int, string> $codes */
        $codes = $menuPositionModel::query()->pluck('code');

        foreach ($codes as $positionCode) {
            $this->cache->forget("menu.tree.{$positionCode}");
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildTree(string $code): array
    {
        /** @var class-string<Model> $menuPositionModel */
        $menuPositionModel = (string) config('moonshine-pages.models.menu_position', MenuPosition::class);

        /** @var MenuPosition|Model|null $position */
        $position = $menuPositionModel::query()->where('code', $code)->first();

        if ($position === null) {
            return [];
        }

        /** @var class-string<Model> $menuModel */
        $menuModel = (string) config('moonshine-pages.models.menu', Menu::class);

        /** @var \Illuminate\Database\Eloquent\Collection<int, Menu|Model> $items */
        $items = $menuModel::query()
            ->active()
            ->whereHas('positions', function ($query) use ($position): void {
                $query->where('menu_positions.id', $position->id);
            })
            ->with(['page', 'positions'])
            ->orderBy('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $byParent = [];

        foreach ($items as $item) {
            $byParent[$item->parent_id ?? 0][] = $item;
        }

        return $this->buildBranch($byParent, 0);
    }

    /**
     * @param  array<int|string, array<int, Menu>>  $byParent
     * @return array<int, array<string, mixed>>
     */
    private function buildBranch(array $byParent, int $parentId): array
    {
        $branch = [];

        $items = $byParent[$parentId] ?? [];

        foreach ($items as $item) {
            $branch[] = [
                'id' => $item->id,
                'name' => $item->name,
                'url' => $item->url,
                'code' => $item->positions->pluck('code')->first(),
                'target' => $item->target,
                'children' => $this->buildBranch($byParent, $item->id),
            ];
        }

        return $branch;
    }
}
