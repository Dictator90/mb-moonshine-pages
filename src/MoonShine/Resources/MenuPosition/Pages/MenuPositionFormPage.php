<?php

declare(strict_types=1);

namespace MB\MoonShine\MoonShine\Resources\MenuPosition\Pages;

use Illuminate\Database\Eloquent\Model;
use MB\MoonShine\Models\Menu;
use MB\MoonShine\MoonShine\Resources\Menu\MenuResource;
use MB\MoonShine\MoonShine\Resources\MenuPosition\MenuPositionResource;
use MB\MoonShine\Support\MoonShinePagesTables;
use Illuminate\Validation\Rule;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\TableBuilderContract;
use MoonShine\Laravel\Fields\Relationships\BelongsToMany;
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
                    Tab::make(__('moonshine-pages::moonshine-pages.menu_position.tabs.menus'), [
                        BelongsToMany::make(
                            __('moonshine-pages::moonshine-pages.menu_position.fields.menus_relation'),
                            'menus',
                            static function (Model $menu): string {
                                if (! $menu instanceof Menu) {
                                    return (string) $menu->getAttribute('name');
                                }

                                $activeLabel = $menu->is_active
                                    ? __('moonshine-pages::moonshine-pages.common.yes')
                                    : __('moonshine-pages::moonshine-pages.common.no');

                                $typeLabel = match ($menu->source_type) {
                                    'link' => __('moonshine-pages::moonshine-pages.menu.source_types.link'),
                                    'page' => __('moonshine-pages::moonshine-pages.menu.source_types.page'),
                                    'route' => __('moonshine-pages::moonshine-pages.menu.source_types.route'),
                                    default => (string) $menu->source_type,
                                };

                                return sprintf('%s — %s — %s', $menu->name, $activeLabel, $typeLabel);
                            },
                            MenuResource::class,
                        )
                            ->asyncSearch()
                            ->modifyTable(static function (TableBuilderContract $table): TableBuilderContract {
                                return $table
                                    ->columnSelection()
                                    ->searchable()
                                    ->queryParamPrefix('menu_position_menus');
                            }),
                    ]),
                ]),
            ]),
        ];
    }

    protected function rules(DataWrapperContract $item): array
    {
        $id = $item->getKey();
        $normalizedId = is_numeric($id) ? (int) $id : null;

        $codeUniqueRule = Rule::unique(MoonShinePagesTables::menuPositions(), 'code');

        if ($normalizedId !== null) {
            $codeUniqueRule->ignore($normalizedId);
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:100', $codeUniqueRule],
            'description' => ['nullable', 'string'],
            'sort_order' => ['integer'],
        ];
    }
}
