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

    /**
     * Whether the built-in SEO fields (seo_title / seo_description) are enabled.
     * Disable via config when the host manages SEO separately or the pages table
     * has no SEO columns. Shared by the resource and its index/form pages.
     */
    public static function seoFieldsEnabled(): bool
    {
        return (bool) config('moonshine-pages.fields.page_seo', true);
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
        $fields = [
            ID::make(),

            Switcher::make(__('moonshine-pages::moonshine-pages.common.is_active'), 'is_active'),

            Text::make(__('moonshine-pages::moonshine-pages.page.fields.title'), 'title'),

            Text::make(__('moonshine-pages::moonshine-pages.page.fields.slug'), 'slug'),

            Preview::make(__('moonshine-pages::moonshine-pages.page.fields.content'), 'content'),
        ];

        if (self::seoFieldsEnabled()) {
            $fields[] = Text::make(__('moonshine-pages::moonshine-pages.page.fields.seo_title'), 'seo_title');
            $fields[] = Textarea::make(__('moonshine-pages::moonshine-pages.page.fields.seo_description'), 'seo_description')
                ->customAttributes(['rows' => 3]);
        }

        $fields[] = Date::make(__('moonshine-pages::moonshine-pages.common.created_at'), 'created_at')
            ->format('d.m.Y H:i')
            ->withTime();

        $fields[] = Date::make(__('moonshine-pages::moonshine-pages.common.updated_at'), 'updated_at')
            ->format('d.m.Y H:i')
            ->withTime();

        return $fields;
    }

    protected function fields(): array
    {
        $fields = [
            ID::make()->sortable(),

            Switcher::make(__('moonshine-pages::moonshine-pages.common.is_active'), 'is_active')
                ->default(true),

            Text::make(__('moonshine-pages::moonshine-pages.page.fields.title'), 'title')
                ->required(),

            Text::make(__('moonshine-pages::moonshine-pages.page.fields.slug'), 'slug')
                ->required(),
        ];

        if (self::seoFieldsEnabled()) {
            $fields[] = Textarea::make(__('moonshine-pages::moonshine-pages.page.fields.seo_title'), 'seo_title')
                ->customAttributes(['rows' => 2]);
            $fields[] = Textarea::make(__('moonshine-pages::moonshine-pages.page.fields.seo_description'), 'seo_description')
                ->customAttributes(['rows' => 3]);
        }

        return $fields;
    }

    protected function search(): array
    {
        return self::seoFieldsEnabled()
            ? ['id', 'title', 'slug', 'seo_title']
            : ['id', 'title', 'slug'];
    }
}
