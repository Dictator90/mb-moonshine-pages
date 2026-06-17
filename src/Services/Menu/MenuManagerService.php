<?php

declare(strict_types=1);

namespace MB\MoonShine\Services\Menu;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use MB\MoonShine\Models\Menu;
use MB\MoonShine\Models\MenuPosition;
use MB\MoonShine\Support\MoonShinePagesTables;

/**
 * Builds the data the Menu Manager admin page renders: each menu position with
 * its full item tree (active AND hidden, unlike the cached frontend
 * {@see MenuRepository}) plus the "add from existing" candidate list.
 *
 * Presentation (badge label/color, target text, icon URL) is resolved here so
 * the Blade views stay thin.
 */
final class MenuManagerService
{
    /**
     * Positions in sort order, each with its nested item tree.
     *
     * @return list<array{id:int,code:string,name:string,items:list<array<string,mixed>>}>
     */
    public function positions(): array
    {
        $positionModel = $this->menuPositionModel();

        /** @var EloquentCollection<int, MenuPosition|Model> $positions */
        $positions = $positionModel::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $result = [];

        foreach ($positions as $position) {
            $result[] = [
                'id' => (int) $position->getKey(),
                'code' => (string) $position->getAttribute('code'),
                'name' => (string) $position->getAttribute('name'),
                'items' => $this->treeForPosition($position),
            ];
        }

        return $result;
    }

    /**
     * Menu items that can be attached to the given position (the modal list).
     * Items already in the position are returned too, flagged `already` so the
     * UI can dim them.
     *
     * @return list<array{id:int,name:string,source_type:string,source_label:string,source_color:string,is_active:bool,already:bool}>
     */
    public function availableFor(int $positionId): array
    {
        $menuModel = $this->menuModel();

        /** @var EloquentCollection<int, Menu|Model> $menus */
        $menus = $menuModel::query()
            ->with('positions')
            ->orderBy('name')
            ->get();

        $result = [];

        foreach ($menus as $menu) {
            $type = (string) $menu->getAttribute('source_type');

            $result[] = [
                'id' => (int) $menu->getKey(),
                'name' => (string) $menu->getAttribute('name'),
                'source_type' => $type,
                'source_label' => $this->sourceLabel($type),
                'source_color' => $this->sourceColor($type),
                'is_active' => (bool) $menu->getAttribute('is_active'),
                'already' => $menu->positions->contains(
                    static fn (Model $p): bool => (int) $p->getKey() === $positionId
                ),
            ];
        }

        return $result;
    }

    /**
     * Build the tree for a position from the pivot's parent_id / sort_order, so a
     * menu can be nested differently in each position it belongs to.
     *
     * @return list<array<string,mixed>>
     */
    private function treeForPosition(MenuPosition|Model $position): array
    {
        $menuModel = $this->menuModel();
        $menusTable = MoonShinePagesTables::menus();
        $pivotTable = MoonShinePagesTables::menuMenuPosition();

        /** @var EloquentCollection<int, Menu|Model> $items */
        $items = $menuModel::query()
            ->join($pivotTable, "{$pivotTable}.menu_id", '=', "{$menusTable}.id")
            ->where("{$pivotTable}.menu_position_id", $position->getKey())
            ->with('page')
            ->orderBy("{$pivotTable}.sort_order")
            ->orderBy("{$menusTable}.name")
            ->get([
                "{$menusTable}.*",
                "{$pivotTable}.parent_id as pivot_parent_id",
                "{$pivotTable}.sort_order as pivot_sort_order",
            ]);

        /** @var array<int|string, list<Menu|Model>> $byParent */
        $byParent = [];

        foreach ($items as $item) {
            $byParent[$item->getAttribute('pivot_parent_id') ?? 0][] = $item;
        }

        return $this->buildBranch($byParent, 0);
    }

    /**
     * @param  array<int|string, list<Menu|Model>>  $byParent
     * @return list<array<string,mixed>>
     */
    private function buildBranch(array $byParent, int $parentId): array
    {
        $branch = [];

        foreach ($byParent[$parentId] ?? [] as $item) {
            $type = (string) $item->getAttribute('source_type');

            $branch[] = [
                'id' => (int) $item->getKey(),
                'name' => (string) $item->getAttribute('name'),
                'is_active' => (bool) $item->getAttribute('is_active'),
                'icon_url' => $this->iconUrl($item),
                'source_type' => $type,
                'source_label' => $this->sourceLabel($type),
                'source_color' => $this->sourceColor($type),
                'target_display' => $this->targetDisplay($item),
                'children' => $this->buildBranch($byParent, (int) $item->getKey()),
            ];
        }

        return $branch;
    }

    private function sourceLabel(string $type): string
    {
        $key = in_array($type, ['none', 'link', 'page', 'route'], true) ? $type : 'none';

        return __("moonshine-pages::moonshine-pages.menu.source_types.{$key}");
    }

    /**
     * MoonShine {@see \MoonShine\Support\Enums\Color} token for the source badge.
     */
    private function sourceColor(string $type): string
    {
        return match ($type) {
            'link' => 'blue',
            'page' => 'green',
            'route' => 'yellow',
            default => 'gray',
        };
    }

    /**
     * The final link shown in the URL/route column. For `page` and `route` we
     * reuse {@see Menu::getUrlAttribute()} so the displayed link reflects exactly
     * how the frontend resolves it — i.e. the menu item's own slug prepended when
     * `prepend_menu_slug` is on, and route parameters substituted into the path.
     * When the target can't be resolved (inactive page / unknown route) we fall
     * back to a readable hint (the route name or the page slug).
     */
    private function targetDisplay(Menu|Model $menu): string
    {
        $type = (string) $menu->getAttribute('source_type');
        $value = $menu->getAttribute('source_value');
        $rawValue = is_string($value) && $value !== '' ? $value : null;

        if ($type === 'link') {
            return $rawValue ?? '—';
        }

        if ($type === 'none') {
            return '—';
        }

        // page / route: resolve through the model's own URL accessor.
        $url = (string) $menu->getAttribute('url');

        if ($url !== '' && $url !== '#') {
            return $this->toDisplayPath($url);
        }

        // Unresolvable: show a hint rather than a dead '#'.
        return match ($type) {
            'route' => $rawValue ?? '—',
            'page' => $menu->page !== null && $menu->page->slug !== null
                ? '/'.ltrim((string) $menu->page->slug, '/')
                : '—',
            default => '—',
        };
    }

    /**
     * Trim an absolute URL to its path (+ query) for a compact column; relative
     * values pass through unchanged.
     */
    private function toDisplayPath(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);

        if (! is_string($path) || $path === '') {
            return $url;
        }

        $query = parse_url($url, PHP_URL_QUERY);

        return $path.(is_string($query) && $query !== '' ? '?'.$query : '');
    }

    private function iconUrl(Menu|Model $menu): ?string
    {
        $image = $menu->getAttribute('image');

        if (! is_string($image) || $image === '') {
            return null;
        }

        return Storage::disk($this->mediaDisk())->url($image);
    }

    private function mediaDisk(): string
    {
        $disk = config('moonshine-pages.media.disk');

        return is_string($disk) && $disk !== ''
            ? $disk
            : (string) config('moonshine.disk', 'public');
    }

    /**
     * @return class-string<Model>
     */
    private function menuModel(): string
    {
        return (string) config('moonshine-pages.models.menu', Menu::class);
    }

    /**
     * @return class-string<Model>
     */
    private function menuPositionModel(): string
    {
        return (string) config('moonshine-pages.models.menu_position', MenuPosition::class);
    }
}
