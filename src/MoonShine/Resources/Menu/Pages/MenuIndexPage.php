<?php

declare(strict_types=1);

namespace MB\MoonShine\MoonShine\Resources\Menu\Pages;

use Illuminate\Database\Eloquent\Model;
use MB\MoonShine\MoonShine\Resources\Menu\MenuResource;
use MB\MoonShine\MoonShine\Resources\MenuPosition\MenuPositionResource;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Fields\Relationships\BelongsToMany;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\UI\Components\Badge;
use MoonShine\UI\Components\Link;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;

/**
 * @extends IndexPage<MenuResource>
 */
final class MenuIndexPage extends IndexPage
{
    protected bool $isColumnSelection = true;

    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make()->sortable(),
            Switcher::make(__('moonshine-pages::moonshine-pages.common.is_active'), 'is_active'),
            Text::make(__('moonshine-pages::moonshine-pages.menu.fields.name'), 'name'),
            BelongsToMany::make(__('moonshine-pages::moonshine-pages.menu.fields.positions'), 'positions', resource: MenuPositionResource::class)
                ->inLine(
                    separator: ' ',
                    badge: fn ($model, $value) => Badge::make((string) $value, 'primary'),
                    link: function (Model $property, $value, $field): string|Link {
                        return Link::make(
                            app(MenuPositionResource::class)->getFormPageUrl($property->id),
                            $value
                        );
                    }
                )
                ->selectMode()
                ->searchable(),
            Text::make(__('moonshine-pages::moonshine-pages.menu.fields.source_type'), 'source_type'),
            Text::make(__('moonshine-pages::moonshine-pages.menu.fields.parent'), 'parent.name'),
            Text::make(__('moonshine-pages::moonshine-pages.menu.fields.page'), 'page.title'),
            Number::make(__('moonshine-pages::moonshine-pages.common.sort_order'), 'sort_order')->sortable(),
            Date::make(__('moonshine-pages::moonshine-pages.common.updated_at'), 'updated_at'),
        ];
    }

    protected function filters(): iterable
    {
        return [
            Text::make(__('moonshine-pages::moonshine-pages.menu.fields.name'), 'name'),
            Text::make(__('moonshine-pages::moonshine-pages.menu.fields.source_type'), 'source_type'),
        ];
    }
}
