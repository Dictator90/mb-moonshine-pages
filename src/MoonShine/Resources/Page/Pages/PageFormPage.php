<?php

declare(strict_types=1);

namespace MB\MoonShine\MoonShine\Resources\Page\Pages;

use Illuminate\Support\Str;
use MB\MoonShine\MoonShine\Resources\Page\PagePublicActionButton;
use MB\MoonShine\MoonShine\Resources\Page\PageResource;
use MB\MoonShine\Support\MoonShinePagesTables;
use MoonShine\CKEditor\Fields\CKEditor;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\ActionButtonContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Fields\Slug;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Tabs;
use MoonShine\UI\Components\Tabs\Tab;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

/**
 * @extends FormPage<PageResource>
 */
final class PageFormPage extends FormPage
{
    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            Box::make([
                Tabs::make([
                    Tab::make(__('moonshine-pages::moonshine-pages.common.tabs.main'), [
                        ID::make()->sortable(),

                        Switcher::make(__('moonshine-pages::moonshine-pages.common.is_active'), 'is_active'),

                        Text::make(__('moonshine-pages::moonshine-pages.page.fields.title'), 'title')
                            ->reactive(lazy: true)
                            ->required(),

                        Slug::make(__('moonshine-pages::moonshine-pages.page.fields.slug'), 'slug')
                            ->from('title')
                            ->live(lazy: true)
                            ->required(),

                        CKEditor::make(__('moonshine-pages::moonshine-pages.page.fields.content'), 'content')
                            ->required(),
                    ]),

                    Tab::make(__('moonshine-pages::moonshine-pages.common.tabs.seo'), [
                        Text::make(__('moonshine-pages::moonshine-pages.page.fields.seo_title'), 'seo_title'),
                        Textarea::make(__('moonshine-pages::moonshine-pages.page.fields.seo_description'), 'seo_description')
                            ->customAttributes(['rows' => 3]),
                    ]),
                ]),
            ]),
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
            $this->modifyDeleteButton(
                $this->getResource()->getDeleteButton(
                    redirectAfterDelete: $this->getResource()->getRedirectAfterDelete(),
                    isAsync: false,
                )
            ),
        ]);
    }

    protected function rules(DataWrapperContract $item): array
    {
        $id = $item->getKey();

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:'.MoonShinePagesTables::pages().',slug,'.$id],
            'content' => ['required', 'string'],
            'is_active' => ['boolean'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function prepareForValidation(): void
    {
        $title = request()->string('title')->toString();

        if (! request()->filled('slug') && $title !== '') {
            request()->merge([
                'slug' => Str::slug($title),
            ]);
        }
    }
}
