<?php

declare(strict_types=1);

namespace MB\MoonShine\MoonShine\Resources\MenuPosition\Pages;

use MB\MoonShine\MoonShine\Resources\MenuPosition\MenuPositionResource;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\UI\Components\Table\TableBuilder;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

/**
 * @extends IndexPage<MenuPositionResource>
 */
final class MenuPositionIndexPage extends IndexPage
{
    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make(__('moonshine-pages::moonshine-pages.menu_position.fields.name'), 'name'),
            Text::make(__('moonshine-pages::moonshine-pages.menu_position.fields.code'), 'code'),
            Textarea::make(__('moonshine-pages::moonshine-pages.menu_position.fields.description'), 'description')
                ->customAttributes(['rows' => 2]),
            Number::make(__('moonshine-pages::moonshine-pages.common.sort_order'), 'sort_order')->sortable(),
            Date::make(__('moonshine-pages::moonshine-pages.common.created_at'), 'created_at'),
            Date::make(__('moonshine-pages::moonshine-pages.common.updated_at'), 'updated_at'),
        ];
    }

    protected function modifyListComponent(ComponentContract $component): ComponentContract
    {
        if ($component instanceof TableBuilder) {
            return $component->columnSelection();
        }

        return $component;
    }

    protected function filters(): iterable
    {
        return [
            Text::make(__('moonshine-pages::moonshine-pages.menu_position.fields.name'), 'name'),
            Text::make(__('moonshine-pages::moonshine-pages.menu_position.fields.code'), 'code'),
            Text::make(__('moonshine-pages::moonshine-pages.menu_position.fields.description'), 'description'),
            Number::make(__('moonshine-pages::moonshine-pages.common.sort_order'), 'sort_order'),
        ];
    }
}
