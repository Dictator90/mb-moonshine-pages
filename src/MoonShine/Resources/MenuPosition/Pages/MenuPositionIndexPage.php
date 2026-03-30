<?php

declare(strict_types=1);

namespace MB\MoonShine\MoonShine\Resources\MenuPosition\Pages;

use MB\MoonShine\MoonShine\Resources\MenuPosition\MenuPositionResource;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Text;

/**
 * @extends IndexPage<MenuPositionResource>
 */
final class MenuPositionIndexPage extends IndexPage
{
    protected bool $isColumnSelection = true;

    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make(__('moonshine-pages::moonshine-pages.menu_position.fields.name'), 'name'),
            Text::make(__('moonshine-pages::moonshine-pages.menu_position.fields.code'), 'code'),
            Number::make(__('moonshine-pages::moonshine-pages.common.sort_order'), 'sort_order')->sortable(),
            Date::make(__('moonshine-pages::moonshine-pages.common.updated_at'), 'updated_at'),
        ];
    }

    protected function filters(): iterable
    {
        return [
            Text::make(__('moonshine-pages::moonshine-pages.menu_position.fields.name'), 'name'),
            Text::make(__('moonshine-pages::moonshine-pages.menu_position.fields.code'), 'code'),
        ];
    }
}
