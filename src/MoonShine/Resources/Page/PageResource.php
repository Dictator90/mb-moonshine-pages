<?php

declare(strict_types=1);

namespace MB\MoonShine\MoonShine\Resources\Page;

use MB\MoonShine\Models\Page;
use MB\MoonShine\MoonShine\Resources\Page\Pages\PageDetailPage;
use MB\MoonShine\MoonShine\Resources\Page\Pages\PageFormPage;
use MB\MoonShine\MoonShine\Resources\Page\Pages\PageIndexPage;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Preview;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

class PageResource extends ModelResource
{
    protected string $model = Page::class;

    protected string $column = 'title';

    protected function onBoot(): void
    {
        $this->model = (string) config('moonshine-pages.models.page', Page::class);
    }

    public function getTitle(): string
    {
        return __('moonshine-pages::moonshine-pages.page.resource_title');
    }

    protected function pages(): array
    {
        return [
            PageIndexPage::class,
            PageFormPage::class,
            PageDetailPage::class,
        ];
    }

    protected function detailFields(): iterable
    {
        return [
            ID::make(),

            Switcher::make(__('moonshine-pages::moonshine-pages.common.is_active'), 'is_active'),

            Text::make(__('moonshine-pages::moonshine-pages.page.fields.title'), 'title'),

            Text::make(__('moonshine-pages::moonshine-pages.page.fields.slug'), 'slug'),

            Preview::make(__('moonshine-pages::moonshine-pages.page.fields.content'), 'content'),

            Text::make(__('moonshine-pages::moonshine-pages.page.fields.seo_title'), 'seo_title'),

            Textarea::make(__('moonshine-pages::moonshine-pages.page.fields.seo_description'), 'seo_description')
                ->customAttributes(['rows' => 3]),

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

            Text::make(__('moonshine-pages::moonshine-pages.page.fields.title'), 'title')
                ->required(),

            Text::make(__('moonshine-pages::moonshine-pages.page.fields.slug'), 'slug')
                ->required(),

            Textarea::make(__('moonshine-pages::moonshine-pages.page.fields.seo_title'), 'seo_title')
                ->customAttributes(['rows' => 2]),

            Textarea::make(__('moonshine-pages::moonshine-pages.page.fields.seo_description'), 'seo_description')
                ->customAttributes(['rows' => 3]),
        ];
    }

    protected function search(): array
    {
        return ['id', 'title', 'slug', 'seo_title'];
    }
}
