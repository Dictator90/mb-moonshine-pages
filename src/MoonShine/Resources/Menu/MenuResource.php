<?php

declare(strict_types=1);

namespace MB\MoonShine\MoonShine\Resources\Menu;

use MB\MoonShine\Models\Menu as MenuModel;
use MB\MoonShine\MoonShine\Resources\Menu\Pages\MenuFormPage;
use MB\MoonShine\MoonShine\Resources\Menu\Pages\MenuIndexPage;
use MB\MoonShine\MoonShine\Resources\MenuPosition\MenuPositionResource;
use MB\MoonShine\MoonShine\Resources\Page\PageResource;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Fields\Relationships\BelongsToMany;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Preview;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;

class MenuResource extends ModelResource
{
    protected string $model = MenuModel::class;

    protected string $column = 'name';

    protected function onBoot(): void
    {
        parent::onBoot();

        $this->model = (string) config('moonshine-pages.models.menu', MenuModel::class);
    }

    public function getTitle(): string
    {
        return __('moonshine-pages::moonshine-pages.menu.resource_title');
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

            BelongsToMany::make(__('moonshine-pages::moonshine-pages.menu.fields.positions'), 'positions', resource: MenuPositionResource::class)
                ->selectMode(),

            Number::make(__('moonshine-pages::moonshine-pages.common.sort_order'), 'sort_order'),

            Preview::make(__('moonshine-pages::moonshine-pages.menu.fields.source_type'), 'source_type')
                ->changePreview(function (mixed $value): string {
                    return match ($value) {
                        'link' => __('moonshine-pages::moonshine-pages.menu.source_types.link'),
                        'page' => __('moonshine-pages::moonshine-pages.menu.source_types.page'),
                        'route' => __('moonshine-pages::moonshine-pages.menu.source_types.route'),
                        default => (string) $value,
                    };
                }),

            Text::make(__('moonshine-pages::moonshine-pages.menu.fields.source_value'), 'source_value'),

            BelongsTo::make(__('moonshine-pages::moonshine-pages.menu.fields.page'), 'page', null, PageResource::class),

            BelongsTo::make(__('moonshine-pages::moonshine-pages.menu.fields.parent'), 'parent', null, self::class),

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

            BelongsToMany::make(__('moonshine-pages::moonshine-pages.menu.fields.positions'), 'positions', resource: MenuPositionResource::class)
                ->selectMode()
                ->searchable(),

            Select::make(__('moonshine-pages::moonshine-pages.menu.fields.source_type'), 'source_type')
                ->options([
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
}
