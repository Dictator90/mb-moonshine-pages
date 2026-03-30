<?php

declare(strict_types=1);

namespace MB\MoonShine\MoonShine\Resources\Page\Pages;

use MB\MoonShine\MoonShine\Resources\Page\PageResource;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;

/**
 * @extends IndexPage<PageResource>
 */
final class PageIndexPage extends IndexPage
{
    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make()->sortable(),
            Switcher::make(__('moonshine-pages::moonshine-pages.common.is_active'), 'is_active'),
            Text::make(__('moonshine-pages::moonshine-pages.page.fields.title'), 'title'),
            Text::make(__('moonshine-pages::moonshine-pages.page.fields.slug'), 'slug'),
            Date::make(__('moonshine-pages::moonshine-pages.common.updated_at'), 'updated_at'),
        ];
    }

    protected function filters(): iterable
    {
        return [
            Text::make(__('moonshine-pages::moonshine-pages.page.fields.title'), 'title'),
            Text::make(__('moonshine-pages::moonshine-pages.page.fields.slug'), 'slug'),
        ];
    }
}
