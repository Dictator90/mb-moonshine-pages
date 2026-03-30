<?php

declare(strict_types=1);

namespace MB\MoonShine\MoonShine\Resources\MenuPosition\Pages;

use MB\MoonShine\MoonShine\Resources\MenuPosition\MenuPositionResource;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Tabs;
use MoonShine\UI\Components\Tabs\Tab;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

/**
 * @extends FormPage<MenuPositionResource>
 */
final class MenuPositionFormPage extends FormPage
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

                        Text::make(__('moonshine-pages::moonshine-pages.menu_position.fields.name'), 'name')
                            ->required(),

                        Text::make(__('moonshine-pages::moonshine-pages.menu_position.fields.code'), 'code')
                            ->required()
                            ->hint(__('moonshine-pages::moonshine-pages.menu_position.hints.code'))
                            ->customAttributes(['maxlength' => 100]),

                        Number::make(__('moonshine-pages::moonshine-pages.common.sort_order'), 'sort_order')
                            ->default(0),

                        Textarea::make(__('moonshine-pages::moonshine-pages.menu_position.fields.description'), 'description')
                            ->customAttributes(['rows' => 2]),
                    ]),
                ]),
            ]),
        ];
    }

    protected function rules(DataWrapperContract $item): array
    {
        $id = $item->getKey();

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:100', 'unique:menu_positions,code,'.$id],
            'description' => ['nullable', 'string'],
            'sort_order' => ['integer'],
        ];
    }
}
