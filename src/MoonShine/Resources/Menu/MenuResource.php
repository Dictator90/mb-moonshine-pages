<?php

declare(strict_types=1);

namespace MB\MoonShine\MoonShine\Resources\Menu;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use MB\MoonShine\Models\Menu as MenuModel;
use MB\MoonShine\MoonShine\Resources\Menu\Pages\MenuFormPage;
use MB\MoonShine\MoonShine\Resources\Menu\Pages\MenuIndexPage;
use MB\MoonShine\Support\MoonShinePagesResources;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Fields\Relationships\BelongsToMany;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Image;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Preview;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;

class MenuResource extends ModelResource
{
    protected string $model = MenuModel::class;

    protected string $column = 'name';

    // Render create/edit forms in a modal — also lets the Menu manager page
    // reuse this resource's form as an async `crud-form` fragment.
    protected bool $createInModal = true;

    protected bool $editInModal = true;

    protected function onBoot(): void
    {
        parent::onBoot();

        $this->model = (string) config('moonshine-pages.models.menu', MenuModel::class);
    }

    public function getTitle(): string
    {
        return __('moonshine-pages::moonshine-pages.menu.resource_title');
    }

    /**
     * Whether the Menu index renders as a parent_id hierarchy (tree) instead of
     * a flat list. Shared with {@see MenuIndexPage} so the name column knows to
     * show indentation.
     */
    public static function indexTreeEnabled(): bool
    {
        return (bool) config('moonshine-pages.menu.index_tree', false);
    }

    /**
     * Tree-ordered options for parent selects/filters: `[key => breadcrumb path]`
     * (e.g. "Tours / Cruises / Sea"), depth-first so children follow their
     * parents and the full ancestry stays visible while searching. Built from
     * {@see orderAsTree}.
     *
     * @return array<array-key, string>
     */
    public static function treeOptions(): array
    {
        /** @var class-string<Model> $menuModel */
        $menuModel = (string) config('moonshine-pages.models.menu', MenuModel::class);

        $options = [];

        foreach (self::orderAsTree($menuModel::query()->get(['id', 'parent_id', 'name', 'sort_order'])) as $menu) {
            $options[$menu->getKey()] = (string) $menu->getAttribute('tree_path');
        }

        return $options;
    }

    /**
     * Tree mode shows the whole hierarchy at once, so pagination is disabled
     * (children must stay next to their parents across "pages").
     */
    public function isPaginationUsed(): bool
    {
        return self::indexTreeEnabled() ? false : parent::isPaginationUsed();
    }

    public function getItems(): iterable|Collection|LazyCollection|CursorPaginator|Paginator
    {
        if (! self::indexTreeEnabled()) {
            return parent::getItems();
        }

        return self::orderAsTree($this->getQuery()->get());
    }

    /**
     * Flatten a flat menu collection into depth-first tree order, tagging each
     * model with `tree_depth` and an indented `tree_name`. Items whose parent is
     * absent from the set (e.g. filtered out) are treated as roots; any items
     * left unreachable (cycles) are appended so nothing is silently dropped.
     *
     * @param  Collection<int, Model>  $items
     * @return Collection<int, Model>
     */
    public static function orderAsTree(Collection $items): Collection
    {
        if ($items->isEmpty()) {
            return $items;
        }

        $keyName = $items->first()->getKeyName();
        $presentKeys = array_flip($items->map(static fn (Model $m): mixed => $m->getAttribute($keyName))->all());

        /** @var array<array-key, list<Model>> $childrenByParent */
        $childrenByParent = [];

        foreach ($items as $item) {
            $parentId = $item->getAttribute('parent_id');
            $bucket = ($parentId !== null && isset($presentKeys[$parentId])) ? $parentId : '__root__';
            $childrenByParent[$bucket][] = $item;
        }

        foreach ($childrenByParent as $bucket => $children) {
            usort($children, static function (Model $a, Model $b) use ($keyName): int {
                return ((int) ($a->getAttribute('sort_order') ?? 0) <=> (int) ($b->getAttribute('sort_order') ?? 0))
                    ?: ($a->getAttribute($keyName) <=> $b->getAttribute($keyName));
            });

            $childrenByParent[$bucket] = $children;
        }

        /** @var Collection<int, Model> $ordered */
        $ordered = new Collection();

        $walk = static function (mixed $bucketKey, int $depth, string $parentPath) use (&$walk, $childrenByParent, $ordered, $keyName): void {
            foreach ($childrenByParent[$bucketKey] ?? [] as $node) {
                $name = (string) $node->getAttribute('name');
                $path = $parentPath === '' ? $name : $parentPath.' / '.$name;

                $node->setAttribute('tree_depth', $depth);
                $node->setAttribute('tree_name', str_repeat('— ', $depth).$name);
                $node->setAttribute('tree_path', $path);

                $ordered->push($node);

                $walk($node->getAttribute($keyName), $depth + 1, $path);
            }
        };

        $walk('__root__', 0, '');

        if ($ordered->count() < $items->count()) {
            $seen = array_flip($ordered->map(static fn (Model $m): mixed => $m->getAttribute($keyName))->all());

            foreach ($items as $item) {
                if (! isset($seen[$item->getAttribute($keyName)])) {
                    $item->setAttribute('tree_depth', 0);
                    $item->setAttribute('tree_name', (string) $item->getAttribute('name'));
                    $item->setAttribute('tree_path', (string) $item->getAttribute('name'));
                    $ordered->push($item);
                }
            }
        }

        return $ordered;
    }

    protected function pages(): array
    {
        return [
            MenuIndexPage::class,
            MenuFormPage::class,
            DetailPage::class,
        ];
    }

    protected function detailFields(): iterable
    {
        return [
            ID::make(),

            Switcher::make(__('moonshine-pages::moonshine-pages.common.is_active'), 'is_active'),

            Text::make(__('moonshine-pages::moonshine-pages.menu.fields.name'), 'name'),

            Image::make(__('moonshine-pages::moonshine-pages.menu.fields.image'), 'image')
                ->disk($this->mediaDisk())
                ->dir($this->mediaDir()),

            BelongsToMany::make(__('moonshine-pages::moonshine-pages.menu.fields.positions'), 'positions', resource: MoonShinePagesResources::menuPosition())
                ->selectMode(),

            Number::make(__('moonshine-pages::moonshine-pages.common.sort_order'), 'sort_order'),

            Preview::make(__('moonshine-pages::moonshine-pages.menu.fields.source_type'), 'source_type')
                ->changePreview(function (mixed $value): string {
                    return match ($value) {
                        'none' => __('moonshine-pages::moonshine-pages.menu.source_types.none'),
                        'link' => __('moonshine-pages::moonshine-pages.menu.source_types.link'),
                        'page' => __('moonshine-pages::moonshine-pages.menu.source_types.page'),
                        'route' => __('moonshine-pages::moonshine-pages.menu.source_types.route'),
                        default => (string) $value,
                    };
                }),

            Text::make(__('moonshine-pages::moonshine-pages.menu.fields.source_value'), 'source_value'),

            BelongsTo::make(__('moonshine-pages::moonshine-pages.menu.fields.page'), 'page', null, MoonShinePagesResources::page()),

            BelongsTo::make(__('moonshine-pages::moonshine-pages.menu.fields.parent'), 'parent', null, MoonShinePagesResources::menu()),

            Preview::make(__('moonshine-pages::moonshine-pages.menu.fields.route_params'), 'route_params')
                ->changePreview(function (mixed $value): string {
                    if ($value === null || $value === '' || $value === []) {
                        return '—';
                    }

                    if (is_string($value)) {
                        return '<pre class="text-sm whitespace-pre-wrap">'.e($value).'</pre>';
                    }

                    try {
                        $json = json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
                    } catch (\JsonException) {
                        return '—';
                    }

                    return '<pre class="text-sm whitespace-pre-wrap">'.e($json).'</pre>';
                }),

            Date::make(__('moonshine-pages::moonshine-pages.common.created_at'), 'created_at')
                ->format('d.m.Y H:i')
                ->withTime(),

            Date::make(__('moonshine-pages::moonshine-pages.common.updated_at'), 'updated_at')
                ->format('d.m.Y H:i')
                ->withTime(),
        ];
    }

    protected function fields(): array
    {
        return [
            ID::make()->sortable(),

            Switcher::make(__('moonshine-pages::moonshine-pages.common.is_active'), 'is_active')
                ->default(true),

            Text::make(__('moonshine-pages::moonshine-pages.menu.fields.name'), 'name')
                ->required(),

            Image::make(__('moonshine-pages::moonshine-pages.menu.fields.image'), 'image')
                ->disk($this->mediaDisk())
                ->dir($this->mediaDir()),

            BelongsToMany::make(__('moonshine-pages::moonshine-pages.menu.fields.positions'), 'positions', resource: MoonShinePagesResources::menuPosition())
                ->selectMode()
                ->searchable(),

            Select::make(__('moonshine-pages::moonshine-pages.menu.fields.source_type'), 'source_type')
                ->options([
                    'none' => __('moonshine-pages::moonshine-pages.menu.source_types.none'),
                    'link' => __('moonshine-pages::moonshine-pages.menu.source_types.link'),
                    'page' => __('moonshine-pages::moonshine-pages.menu.source_types.page'),
                    'route' => __('moonshine-pages::moonshine-pages.menu.source_types.route'),
                ])
                ->required(),

            Number::make(__('moonshine-pages::moonshine-pages.common.sort_order'), 'sort_order')
                ->default(0),
        ];
    }

    protected function search(): array
    {
        return ['id', 'name', 'source_type'];
    }

    private function mediaDisk(): string
    {
        $disk = config('moonshine-pages.media.disk');

        return is_string($disk) && $disk !== ''
            ? $disk
            : (string) config('moonshine.disk', 'public');
    }

    private function mediaDir(): string
    {
        return (string) config('moonshine-pages.media.image_dir', 'menu');
    }
}
