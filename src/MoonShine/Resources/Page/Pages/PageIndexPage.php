<?php

declare(strict_types=1);

namespace MB\MoonShine\MoonShine\Resources\Page\Pages;

use MB\MoonShine\MoonShine\Resources\Page\PagePublicActionButton;
use MB\MoonShine\MoonShine\Resources\Page\PageResource;
use MoonShine\Contracts\UI\ActionButtonContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Support\ListOf;
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

    /**
     * @return ListOf<ActionButtonContract>
     */
    protected function buttons(): ListOf
    {
        return new ListOf(ActionButtonContract::class, [
            $this->modifyDetailButton(
                $this->getResource()->getDetailButton()
            ),
            PagePublicActionButton::make(),
            $this->modifyEditButton(
                $this->getResource()->getEditButton(
                    isAsync: $this->isAsync(),
                )
            ),
            $this->modifyDeleteButton(
                $this->getResource()->getDeleteButton(
                    redirectAfterDelete: $this->getResource()->getRedirectAfterDelete(),
                    isAsync: $this->isAsync(),
                )
            ),
            $this->modifyMassDeleteButton(
                $this->getResource()->getMassDeleteButton(
                    redirectAfterDelete: $this->getResource()->getRedirectAfterDelete(),
                    isAsync: $this->isAsync(),
                )
            ),
        ]);
    }

    protected function filters(): iterable
    {
        return [
            Text::make(__('moonshine-pages::moonshine-pages.page.fields.title'), 'title'),
            Text::make(__('moonshine-pages::moonshine-pages.page.fields.slug'), 'slug'),
        ];
    }
}
