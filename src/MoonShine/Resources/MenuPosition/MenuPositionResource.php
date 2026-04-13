<?php

declare(strict_types=1);

namespace MB\MoonShine\MoonShine\Resources\MenuPosition;

use MB\MoonShine\Models\MenuPosition;
use MB\MoonShine\MoonShine\Resources\MenuPosition\Pages\MenuPositionFormPage;
use MB\MoonShine\MoonShine\Resources\MenuPosition\Pages\MenuPositionIndexPage;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

class MenuPositionResource extends ModelResource
{
    protected string $model = MenuPosition::class;

    protected string $column = 'name';

    protected function onBoot(): void
    {
        parent::onBoot();

        $this->model = (string) config('moonshine-pages.models.menu_position', MenuPosition::class);
    }

    public function getTitle(): string
    {
        return __('moonshine-pages::moonshine-pages.menu_position.resource_title');
    }

    protected function pages(): array
    {
        return [
            MenuPositionIndexPage::class,
            MenuPositionFormPage::class,
            DetailPage::class,
        ];
    }

    protected function detailFields(): iterable
    {
        return [
            ID::make(),

            Text::make(__('moonshine-pages::moonshine-pages.menu_position.fields.name'), 'name'),

            Text::make(__('moonshine-pages::moonshine-pages.menu_position.fields.code'), 'code'),

            Number::make(__('moonshine-pages::moonshine-pages.common.sort_order'), 'sort_order'),

            Textarea::make(__('moonshine-pages::moonshine-pages.menu_position.fields.description'), 'description')
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
        ];
    }

    protected function search(): array
    {
        return ['id', 'name', 'code'];
    }
}
