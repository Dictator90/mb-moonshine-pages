<?php

declare(strict_types=1);

namespace MB\MoonShine\MoonShine\Resources\Menu\Pages;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Route as IlluminateRoute;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use MB\MoonShine\MoonShine\Resources\Menu\MenuResource;
use MB\MoonShine\MoonShine\Resources\MenuPosition\MenuPositionResource;
use MB\MoonShine\MoonShine\Resources\Page\PageResource;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Fields\Relationships\BelongsToMany;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Flex;
use MoonShine\UI\Components\Tabs;
use MoonShine\UI\Components\Tabs\Tab;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;

/**
 * @extends FormPage<MenuResource>
 */
final class MenuFormPage extends FormPage
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

                        Flex::make([
                            Flex::make([
                                Date::make(__('moonshine-pages::moonshine-pages.common.created_at'), 'created_at')->disabled()->withTime(),
                            ]),
                            Flex::make([
                                Date::make(__('moonshine-pages::moonshine-pages.common.updated_at'), 'updated_at')->disabled()->withTime(),
                            ]),
                        ], justifyAlign: 'left'),

                        Switcher::make(__('moonshine-pages::moonshine-pages.common.is_active'), 'is_active')
                            ->default(true),

                        Text::make(__('moonshine-pages::moonshine-pages.menu.fields.name'), 'name')
                            ->required(),

                        BelongsToMany::make(__('moonshine-pages::moonshine-pages.menu.fields.positions'), 'positions', resource: MenuPositionResource::class)
                            ->selectMode()
                            ->searchable(),

                        Number::make(__('moonshine-pages::moonshine-pages.common.sort_order'), 'sort_order')
                            ->default(0),

                        Select::make(__('moonshine-pages::moonshine-pages.menu.fields.source_type'), 'source_type')
                            ->options([
                                'link' => __('moonshine-pages::moonshine-pages.menu.source_types.link'),
                                'page' => __('moonshine-pages::moonshine-pages.menu.source_types.page'),
                                'route' => __('moonshine-pages::moonshine-pages.menu.source_types.route'),
                            ])
                            ->default('link')
                            ->required()
                            ->mergeAttribute(
                                'x-on:change',
                                $this->showWhenRefreshDispatchExpression(),
                                ';'
                            ),

                        Text::make(__('moonshine-pages::moonshine-pages.menu.fields.link'), 'link')
                            ->fromRaw(function (mixed $raw): ?string {
                                return data_get($raw, 'source_type') === 'link'
                                    ? (string) data_get($raw, 'source_value')
                                    : null;
                            })
                            ->onApply(function (mixed $menu, mixed $value): mixed {
                                if (data_get(request()->all(), 'source_type') === 'link') {
                                    data_set($menu, 'source_value', $value);
                                }

                                return $menu;
                            })
                            ->showWhen('source_type', 'link')
                            ->hint(__('moonshine-pages::moonshine-pages.menu.hints.link')),

                        BelongsTo::make(__('moonshine-pages::moonshine-pages.menu.fields.page'), 'page', null, PageResource::class)
                            ->nullable()
                            ->showWhen('source_type', 'page'),

                        Select::make(__('moonshine-pages::moonshine-pages.menu.fields.route'), 'source_value')
                            ->nullable()
                            ->options($this->getRouteOptions())
                            ->showWhen('source_type', 'route')
                            ->mergeAttribute(
                                'x-on:change',
                                $this->showWhenRefreshDispatchExpression(),
                                ';'
                            ),

                        ...$this->getRouteParameterFields(),

                        BelongsTo::make(__('moonshine-pages::moonshine-pages.menu.fields.parent'), 'parent', null, MenuResource::class)
                            ->nullable()
                            ->valuesQuery(function (Builder $query, BelongsTo $field): Builder {
                                $original = $field->getData()?->getOriginal();

                                if ($original instanceof Model && $original->getKey() !== null) {
                                    $query->whereKeyNot($original->getKey());
                                }

                                return $query;
                            }),
                    ]),
                ]),
            ]),
        ];
    }

    protected function rules(DataWrapperContract $item): array
    {
        $id = $item->getKey();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'sort_order' => ['integer'],
            'source_type' => ['required', 'string', 'in:link,page,route'],
            'link' => ['nullable', 'string', 'max:255'],
            'route' => ['nullable', 'string', 'max:255'],
            'source_value' => ['nullable', 'string', 'max:255'],
            'route_params' => ['nullable', 'array'],
            'parent_id' => array_values(array_filter([
                'nullable',
                'integer',
                $id !== null ? Rule::notIn([(int) $id]) : null,
            ])),
        ];

        foreach ($this->extractRouteParameters($this->resolveSelectedRouteName()) as $parameter) {
            $rules["route_params.$parameter"] = ['nullable', 'string', 'max:255'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    private function getRouteOptions(): array
    {
        $options = [];

        foreach (Route::getRoutes() as $route) {
            if (! in_array('GET', $route->methods(), true)) {
                continue;
            }

            $name = $route->getName();

            if ($name === null) {
                continue;
            }

            if (str_starts_with($route->uri(), 'admin') || str_starts_with($route->uri(), 'api')) {
                continue;
            }

            $options[$name] = sprintf(
                '%s (%s)',
                $name,
                $route->uri()
            );
        }

        ksort($options);

        return $options;
    }

    /**
     * @return list<FieldContract>
     */
    private function getRouteParameterFields(): array
    {
        $routeParametersMap = $this->getRouteParametersMap();

        $fields = [];

        foreach ($this->getParametersToRoutesMap($routeParametersMap) as $parameter => $routeNames) {
            $fields[] = Text::make(__('moonshine-pages::moonshine-pages.menu.fields.route_parameter', ['parameter' => $parameter]), "route_params.$parameter")
                ->fromRaw(fn (mixed $raw): ?string => data_get($raw, "route_params.$parameter"))
                ->onApply(function (mixed $menu, mixed $value) use ($parameter): mixed {
                    $routeParams = data_get($menu, 'route_params');

                    if (! is_array($routeParams)) {
                        $routeParams = [];
                    }

                    $stringValue = is_scalar($value) ? trim((string) $value) : '';

                    if ($stringValue === '') {
                        unset($routeParams[$parameter]);
                    } else {
                        $routeParams[$parameter] = $stringValue;
                    }

                    data_set($menu, 'route_params', $routeParams);

                    return $menu;
                })
                ->showWhen('source_type', 'route')
                ->showWhen('source_value', 'in', $routeNames)
                ->hint(__('moonshine-pages::moonshine-pages.menu.hints.route_parameter'));
        }

        return $fields;
    }

    private function resolveSelectedRouteName(): ?string
    {
        $uriKey = $this->getResource()->getUriKey();

        $fromReactive = request()->input('values.source_value');
        if (is_string($fromReactive) && $fromReactive !== '') {
            return $fromReactive;
        }

        $nested = data_get(request()->all(), $uriKey.'.source_value');
        if (is_string($nested) && $nested !== '') {
            return $nested;
        }

        $sourceValueFromRequest = request()->string('source_value')->toString();

        if ($sourceValueFromRequest !== '') {
            return $sourceValueFromRequest;
        }

        $routeFromRequest = request()->string('route')->toString();

        if ($routeFromRequest !== '') {
            return $routeFromRequest;
        }

        $item = $this->getResource()->getItem();

        if ($item === null) {
            return null;
        }

        $sourceType = (string) data_get($item, 'source_type', '');
        $sourceValue = (string) data_get($item, 'source_value', '');

        return $sourceType === 'route' && $sourceValue !== '' ? $sourceValue : null;
    }

    /**
     * @return list<string>
     */
    private function extractRouteParameters(?string $routeName): array
    {
        if ($routeName === null || $routeName === '') {
            return [];
        }

        /** @var IlluminateRoute|null $route */
        $route = Route::getRoutes()->getByName($routeName);

        if ($route === null) {
            return [];
        }

        preg_match_all('/\{([^}]+)\}/', $route->uri(), $matches);

        $parameters = [];

        foreach ($matches[1] ?? [] as $parameter) {
            if (! is_string($parameter)) {
                continue;
            }

            $cleanParameter = trim(str_replace('?', '', $parameter));

            if ($cleanParameter === '') {
                continue;
            }

            $parameters[] = $cleanParameter;
        }

        return array_values(array_unique($parameters));
    }

    /**
     * @return array<string, list<string>>
     */
    private function getRouteParametersMap(): array
    {
        $map = [];

        foreach (array_keys($this->getRouteOptions()) as $routeName) {
            $map[$routeName] = $this->extractRouteParameters($routeName);
        }

        return $map;
    }

    /**
     * @param  array<string, list<string>>  $routeParametersMap
     * @return array<string, list<string>>
     */
    private function getParametersToRoutesMap(array $routeParametersMap): array
    {
        $parametersToRoutesMap = [];

        foreach ($routeParametersMap as $routeName => $parameters) {
            foreach ($parameters as $parameter) {
                $parametersToRoutesMap[$parameter] ??= [];
                $parametersToRoutesMap[$parameter][] = $routeName;
            }
        }

        foreach ($parametersToRoutesMap as $parameter => $routeNames) {
            $parametersToRoutesMap[$parameter] = array_values(array_unique($routeNames));
        }

        ksort($parametersToRoutesMap);

        return $parametersToRoutesMap;
    }

    /**
     * After {@see Select} gains a real `name` in the DOM, MoonShine must re-run `whenFieldsInit`
     * so `showWhen` conditions that depend on `source_value` are registered (skipped on first init
     * while the route select is hidden).
     */
    private function showWhenRefreshDispatchExpression(): string
    {
        $formName = $this->getResource()->getUriKey();

        return "\$nextTick(() => \$dispatch('show_when_refresh:{$formName}'))";
    }
}
