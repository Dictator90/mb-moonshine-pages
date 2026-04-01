<?php

declare(strict_types=1);

namespace MB\MoonShine\MoonShine\Resources\Menu;

use MB\MoonShine\Models\Menu as MenuModel;
use MB\MoonShine\MoonShine\Resources\Menu\Pages\MenuFormPage;
use MB\MoonShine\MoonShine\Resources\Menu\Pages\MenuIndexPage;
use MB\MoonShine\MoonShine\Resources\MenuPosition\MenuPositionResource;
use MoonShine\Crud\Pages\DetailPage;
use MoonShine\Laravel\Fields\Relationships\BelongsToMany;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\MenuManager\Attributes\Group;
use MoonShine\Support\Attributes\Icon;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
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
