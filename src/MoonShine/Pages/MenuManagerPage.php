<?php

declare(strict_types=1);

namespace MB\MoonShine\MoonShine\Pages;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use MB\MoonShine\Models\Menu;
use MB\MoonShine\Services\Menu\MenuManagerService;
use MB\MoonShine\Services\Menu\MenuRepository;
use MB\MoonShine\Support\MoonShinePagesResources;
use MB\MoonShine\Support\MoonShinePagesTables;
use MoonShine\Contracts\Core\CrudResourceContract;
use MoonShine\Laravel\Pages\Page;
use MoonShine\Support\Attributes\AsyncMethod;
use MoonShine\UI\Components\FlexibleRender;

/**
 * Standalone admin page that manages menu items grouped by position
 * (drag & drop within a position, hide/show, duplicate, detach, delete,
 * "add from existing"). Routed by MoonShine at /{prefix}/page/menu-manager-page.
 *
 * The visual is a custom Blade view (native MoonShine theme); row actions call
 * the {@see AsyncMethod} handlers below through the MoonShine `method` endpoint.
 */
final class MenuManagerPage extends Page
{
    /**
     * Session key holding the list<int> of item ids the user has expanded in the
     * tree. Absence ⇒ collapsed, so by default every item starts collapsed.
     */
    private const EXPANDED_SESSION_KEY = 'moonshine-pages.menu_manager.expanded';

    public function getTitle(): string
    {
        return __('moonshine-pages::moonshine-pages.menu_manager.title');
    }

    /**
     * @return array<string, string>
     */
    public function getBreadcrumbs(): array
    {
        return ['#' => $this->getTitle()];
    }

    protected function components(): iterable
    {
        return [
            FlexibleRender::make(
                view('moonshine-pages::menu-manager.index', $this->indexViewData())
            ),
        ];
    }

    // ─────────────────────────── Async actions ───────────────────────────
    //
    // The client calls these through the MoonShine `method` endpoint via
    // MoonShine.request() (CSRF handled by axios). Structural changes return a
    // redirect to the referring page so the client reloads and re-renders the
    // tree; toggle is applied optimistically client-side; reorder is silent.

    #[AsyncMethod]
    public function toggleActive(Request $request): void
    {
        $menu = $this->findMenu((int) $request->input('id'));

        if ($menu !== null) {
            $menu->setAttribute('is_active', ! $menu->getAttribute('is_active'));
            $menu->save();
        }
    }

    /**
     * Persist a row's expanded/collapsed state in the user's session so the tree
     * remembers it across reloads. Fire-and-forget (no body returned); the client
     * has already flipped the row optimistically.
     */
    #[AsyncMethod]
    public function toggleCollapse(Request $request): void
    {
        $id = (int) $request->input('id');

        if ($id <= 0) {
            return;
        }

        $open = filter_var($request->input('open'), FILTER_VALIDATE_BOOLEAN);

        $set = array_fill_keys($this->expandedIds(), true);

        if ($open) {
            $set[$id] = true;
        } else {
            unset($set[$id]);
        }

        session()->put(self::EXPANDED_SESSION_KEY, array_map('intval', array_keys($set)));
    }

    #[AsyncMethod]
    public function duplicate(Request $request): RedirectResponse
    {
        $menu = $this->findMenu((int) $request->input('id'));

        if ($menu !== null) {
            /** @var Menu|Model $copy */
            $copy = $menu->replicate();
            $copy->setAttribute('name', trim((string) $menu->getAttribute('name').' '.__('moonshine-pages::moonshine-pages.menu_manager.copy_suffix')));
            $copy->save();

            // Copy every placement (position + per-position parent + order) so the
            // duplicate sits beside the original in each of its positions.
            $placements = DB::table($this->pivotTable())
                ->where('menu_id', $menu->getKey())
                ->get(['menu_position_id', 'parent_id', 'sort_order']);

            foreach ($placements as $placement) {
                DB::table($this->pivotTable())->insert([
                    'menu_id' => $copy->getKey(),
                    'menu_position_id' => $placement->menu_position_id,
                    'parent_id' => $placement->parent_id,
                    'sort_order' => $placement->sort_order,
                ]);
            }

            $this->repository()->forget();
        }

        return $this->reload($request);
    }

    #[AsyncMethod]
    public function detach(Request $request): RedirectResponse
    {
        $menu = $this->findMenu((int) $request->input('id'));
        $positionId = (int) $request->input('position');

        if ($menu !== null && $positionId > 0) {
            // Promote this position's children to the item's own parent there,
            // then drop only this placement (other positions are untouched).
            $ownParent = DB::table($this->pivotTable())
                ->where('menu_id', $menu->getKey())
                ->where('menu_position_id', $positionId)
                ->value('parent_id');

            DB::table($this->pivotTable())
                ->where('menu_position_id', $positionId)
                ->where('parent_id', $menu->getKey())
                ->update(['parent_id' => $ownParent]);

            $menu->positions()->detach($positionId);
            $this->repository()->forget();
        }

        return $this->reload($request);
    }

    #[AsyncMethod]
    public function destroy(Request $request): RedirectResponse
    {
        $menu = $this->findMenu((int) $request->input('id'));

        if ($menu !== null) {
            // In every position, promote the item's children to its own parent
            // there before deleting (pivots cascade with the menu row).
            $placements = DB::table($this->pivotTable())
                ->where('menu_id', $menu->getKey())
                ->get(['menu_position_id', 'parent_id']);

            foreach ($placements as $placement) {
                DB::table($this->pivotTable())
                    ->where('menu_position_id', $placement->menu_position_id)
                    ->where('parent_id', $menu->getKey())
                    ->update(['parent_id' => $placement->parent_id]);
            }

            $menu->delete();
            $this->repository()->forget();
        }

        return $this->reload($request);
    }

    #[AsyncMethod]
    public function attach(Request $request): RedirectResponse
    {
        $positionId = (int) $request->input('position');

        /** @var list<int> $ids */
        $ids = array_values(array_filter(array_map('intval', (array) $request->input('ids', []))));

        if ($positionId > 0 && $ids !== []) {
            $next = (int) DB::table($this->pivotTable())
                ->where('menu_position_id', $positionId)
                ->max('sort_order') + 1;

            foreach ($ids as $id) {
                $menu = $this->findMenu($id);

                if ($menu === null) {
                    continue;
                }

                $exists = DB::table($this->pivotTable())
                    ->where('menu_id', $id)
                    ->where('menu_position_id', $positionId)
                    ->exists();

                if (! $exists) {
                    // New items attach as roots of the position; arrange via drag.
                    $menu->positions()->attach($positionId, ['parent_id' => null, 'sort_order' => $next++]);
                }
            }

            $this->repository()->forget();
        }

        return $this->reload($request);
    }

    /**
     * Persist a within-position drag: set the moved item's parent and re-number
     * its siblings — all on the pivot for THIS position, so the same item can be
     * nested differently in other positions. Fed by MoonShine's Sortable helper
     * (`id`, `parent`, `data`) plus the position id carried in the endpoint URL.
     *
     * A cross-list move fires onSort on both source and destination lists; only
     * the destination event carries the moved id inside `data`, so the other is
     * skipped. Nothing is returned — the DOM is already correct.
     */
    #[AsyncMethod]
    public function reorder(Request $request): void
    {
        $id = (int) $request->input('id');
        $positionId = (int) $request->input('position');

        if ($id <= 0 || $positionId <= 0) {
            return;
        }

        $dataRaw = (string) $request->input('data', '');
        /** @var list<int> $order */
        $order = array_values(array_filter(array_map('intval', $dataRaw === '' ? [] : explode(',', $dataRaw))));

        // Ignore the source-list event of a cross-list move (moved id absent).
        if (! in_array($id, $order, true)) {
            return;
        }

        $parentRaw = $request->input('parent');
        $parentId = is_numeric($parentRaw) ? (int) $parentRaw : null;

        // Guard against cycles within this position's tree.
        if ($parentId !== null && ($parentId === $id || $this->isDescendantInPosition($parentId, $id, $positionId))) {
            return;
        }

        DB::table($this->pivotTable())
            ->where('menu_id', $id)
            ->where('menu_position_id', $positionId)
            ->update(['parent_id' => $parentId]);

        foreach ($order as $index => $orderedId) {
            DB::table($this->pivotTable())
                ->where('menu_id', $orderedId)
                ->where('menu_position_id', $positionId)
                ->update(['sort_order' => $index]);
        }

        $this->repository()->forget();
    }

    /**
     * Redirect the async caller back to the page it came from so the client
     * reloads and re-renders the tree after a structural change.
     */
    private function reload(Request $request): RedirectResponse
    {
        $referer = $request->headers->get('referer');

        return redirect()->to(is_string($referer) && $referer !== '' ? $referer : $this->getUrl());
    }

    // ─────────────────────────── View data ───────────────────────────

    /**
     * @return array<string, mixed>
     */
    private function indexViewData(): array
    {
        return [
            'title' => $this->getTitle(),
            'positions' => $this->positionsData(),
            'createUrl' => $this->formFragmentUrl($this->resource()),
        ];
    }

    /**
     * The per-position view-data sets, shared by the full page and the in-place
     * tree re-render.
     *
     * @return list<array<string, mixed>>
     */
    private function positionsData(): array
    {
        return array_map(
            fn (array $position): array => $this->positionViewData($position),
            $this->service()->positions()
        );
    }

    /**
     * @param  array{id:int,code:string,name:string,items:list<array<string,mixed>>}  $position
     * @return array<string, mixed>
     */
    private function positionViewData(array $position): array
    {
        $resource = $this->resource();

        $position['items'] = $this->decorateItems(
            $position['items'],
            $resource,
            array_fill_keys($this->expandedIds(), true),
        );

        return [
            'position' => $position,
            'available' => $this->service()->availableFor($position['id']),
            'endpoints' => $this->endpoints(),
            'createUrl' => $this->formFragmentUrl($resource),
        ];
    }

    /**
     * URL of the Menu resource create/edit form rendered as the async `crud-form`
     * fragment, loaded into the shared modal via MoonShine.ui.toggleModal(). The
     * form's own fields rely on the global MoonShine bundle already on this page.
     */
    private function formFragmentUrl(CrudResourceContract $resource, int|string|null $key = null): string
    {
        return $resource->getFormPageUrl(
            $key,
            [
                '_component_name' => $resource->getListComponentName(),
                '_async_form' => true,
            ],
            fragment: 'crud-form',
        );
    }

    /**
     * Inject the per-item edit URL (the reused Menu resource form fragment) and
     * the initial expanded/collapsed state (from the session set; default false).
     *
     * @param  list<array<string,mixed>>  $items
     * @param  array<int,true>  $expandedSet
     * @return list<array<string,mixed>>
     */
    private function decorateItems(array $items, CrudResourceContract $resource, array $expandedSet): array
    {
        foreach ($items as &$item) {
            /** @var int $id */
            $id = $item['id'];
            $item['edit_url'] = $this->formFragmentUrl($resource, $id);
            $item['open'] = isset($expandedSet[$id]);
            /** @var list<array<string,mixed>> $children */
            $children = $item['children'];
            $item['children'] = $this->decorateItems($children, $resource, $expandedSet);
        }

        unset($item);

        return $items;
    }

    /**
     * Item ids the user has expanded, from the session (empty ⇒ all collapsed).
     *
     * @return list<int>
     */
    private function expandedIds(): array
    {
        /** @var array<array-key, mixed> $ids */
        $ids = (array) session()->get(self::EXPANDED_SESSION_KEY, []);

        return array_values(array_unique(array_map('intval', $ids)));
    }

    /**
     * Base URLs for each async action; the client appends id/position/order.
     *
     * @return array<string, string>
     */
    private function endpoints(): array
    {
        $endpoints = $this->getCore()->getRouter()->getEndpoints();

        $methods = ['toggleActive', 'toggleCollapse', 'duplicate', 'detach', 'destroy', 'attach', 'reorder'];

        $urls = [];

        foreach ($methods as $method) {
            $urls[$method] = $endpoints->method(method: $method, page: $this);
        }

        return $urls;
    }

    // ─────────────────────────── Helpers ───────────────────────────

    /**
     * Walk the pivot parent chain of $candidateId within $positionId; true if
     * $ancestorId is one of its ancestors (so making it the parent would cycle).
     */
    private function isDescendantInPosition(int $candidateId, int $ancestorId, int $positionId): bool
    {
        $current = $candidateId;
        $guard = 0;

        while ($current !== null && $guard++ < 1000) {
            if ($current === $ancestorId) {
                return true;
            }

            $parent = DB::table($this->pivotTable())
                ->where('menu_id', $current)
                ->where('menu_position_id', $positionId)
                ->value('parent_id');

            $current = $parent !== null ? (int) $parent : null;
        }

        return false;
    }

    private function findMenu(int $id): Menu|Model|null
    {
        if ($id <= 0) {
            return null;
        }

        return $this->menuModel()::query()->find($id);
    }

    private function service(): MenuManagerService
    {
        return app(MenuManagerService::class);
    }

    private function repository(): MenuRepository
    {
        return app(MenuRepository::class);
    }

    private function resource(): CrudResourceContract
    {
        /** @var class-string<CrudResourceContract> $class */
        $class = MoonShinePagesResources::menu();

        /** @var CrudResourceContract $resource */
        $resource = $this->getCore()->getContainer($class);

        return $resource;
    }

    /**
     * @return class-string<Model>
     */
    private function menuModel(): string
    {
        return (string) config('moonshine-pages.models.menu', Menu::class);
    }

    private function pivotTable(): string
    {
        return MoonShinePagesTables::menuMenuPosition();
    }
}
