<?php

declare(strict_types=1);

namespace MB\MoonShine\MoonShine\Resources\Page\Pages;

use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use MB\MoonShine\MoonShine\Resources\Page\PagePublicActionButton;
use MB\MoonShine\MoonShine\Resources\Page\PageResource;
use MB\MoonShine\Support\MoonShinePagesTables;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\ActionButtonContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\TinyMce\Fields\TinyMce;
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
class PageFormPage extends FormPage
{
    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
    {
        $tabs = [
            Tab::make(__('moonshine-pages::moonshine-pages.common.tabs.main'), [
                ID::make()->sortable(),

                Switcher::make(__('moonshine-pages::moonshine-pages.common.is_active'), 'is_active'),

                Text::make(__('moonshine-pages::moonshine-pages.page.fields.title'), 'title')
                    ->reactive(lazy: true)
                    ->required(),

                Text::make(__('moonshine-pages::moonshine-pages.page.fields.slug'), 'slug')
                    ->required()
                    ->hint(__('moonshine-pages::moonshine-pages.page.hints.slug')),

                $this->resolveContentField()
                    ->required(),
            ]),
        ];

        if (PageResource::seoFieldsEnabled()) {
            $tabs[] = Tab::make(__('moonshine-pages::moonshine-pages.common.tabs.seo'), [
                Text::make(__('moonshine-pages::moonshine-pages.page.fields.seo_title'), 'seo_title'),
                Textarea::make(__('moonshine-pages::moonshine-pages.page.fields.seo_description'), 'seo_description')
                    ->customAttributes(['rows' => 3]),
            ]);
        }

        return [
            Box::make([
                Tabs::make($tabs),
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
        $slugUnique = Rule::unique(MoonShinePagesTables::pages(), 'slug');

        $id = $item->getKey();

        if ($id !== null && $id !== '') {
            $slugUnique = $slugUnique->ignore($id);
        }

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            // `#` delimiter (not `/`): the slug pattern may contain `/` for multi-segment slugs.
            'slug' => ['required', 'string', 'max:255', 'regex:#'.$this->slugFormatPattern().'#', $slugUnique],
            'content' => ['required', 'string'],
            'is_active' => ['boolean'],
        ];

        if (PageResource::seoFieldsEnabled()) {
            $rules['seo_title'] = ['nullable', 'string', 'max:255'];
            $rules['seo_description'] = ['nullable', 'string', 'max:500'];
        }

        return $rules;
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

    /**
     * Resolve the configured content editor field. Falls back to a plain
     * Textarea when the configured class is missing or not a MoonShine field,
     * so a misconfiguration degrades gracefully instead of fataling.
     */
    private function resolveContentField(): FieldContract
    {
        /** @var mixed $configured */
        $configured = config('moonshine-pages.fields.page_content', TinyMce::class);

        $label = __('moonshine-pages::moonshine-pages.page.fields.content');

        if (is_string($configured) && is_subclass_of($configured, FieldContract::class)) {
            /** @var FieldContract $field */
            $field = $configured::make($label, 'content');

            return $field;
        }

        return Textarea::make($label, 'content');
    }

    /**
     * The slug format pattern (without the reserved-slug lookahead) used to
     * validate the slug field. Mirrors the public route slug constraint so
     * multi-segment slugs like "catalog/feature" are accepted.
     */
    private function slugFormatPattern(): string
    {
        return (string) config(
            'moonshine-pages.route.slug_pattern',
            '^[A-Za-z0-9-_]+(?:/[A-Za-z0-9-_]+)*$'
        );
    }
}
