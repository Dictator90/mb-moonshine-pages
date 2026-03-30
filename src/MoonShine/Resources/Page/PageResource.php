<?php

declare(strict_types=1);

namespace MB\MoonShine\MoonShine\Resources\Page;

use MB\MoonShine\Models\Page;
use MB\MoonShine\MoonShine\Resources\Page\Pages\PageFormPage;
use MB\MoonShine\MoonShine\Resources\Page\Pages\PageIndexPage;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\MenuManager\Attributes\Group;
use MoonShine\Support\Attributes\Icon;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

#[Icon('document-text')]
#[Group('moonshine-pages::moonshine-pages.menu_group.content', 'document-text', translatable: true)]
class PageResource extends ModelResource
{
    protected string $model = Page::class;

    protected string $column = 'title';

    protected function onBoot(): void
    {
        parent::onBoot();

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
        return ['id', 'title', 'slug'];
    }
}
