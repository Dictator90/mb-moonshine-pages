<?php

declare(strict_types=1);

namespace MB\MoonShine\MoonShine\Resources\Menu\Pages;

use Illuminate\Routing\Route as IlluminateRoute;
use Illuminate\Support\Facades\Route;
use MB\MoonShine\MoonShine\Resources\Menu\MenuResource;
use MB\MoonShine\Support\MoonShinePagesResources;
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
use MoonShine\UI\Fields\Image;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;

/**
 * @extends FormPage<MenuResource>
 */
class MenuFormPage extends FormPage
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

                        Text::make(__('moonshine-pages::moonshine-pages.menu.fields.name'), 'name')
                            ->required(),

                        Switcher::make(__('moonshine-pages::moonshine-pages.common.is_active'), 'is_active')
                            ->default(true),

                        Select::make(__('moonshine-pages::moonshine-pages.menu.fields.source_type'), 'source_type')
                            ->options([
                                'none' => __('moonshine-pages::moonshine-pages.menu.source_types.none'),
                                'link' => __('moonshine-pages::moonshine-pages.menu.source_types.link'),
                                'page' => __('moonshine-pages::moonshine-pages.menu.source_types.page'),
                                'route' => __('moonshine-pages::moonshine-pages.menu.source_types.route'),
                            ])
                            ->default('none')
                            ->required()
                            ->mergeAttribute(
                                'x-on:change',
                                $this->showWhenRefreshDispatchExpression(),
                                ';'
                            ),

                        Text::make(__('moonshine-pages::moonshine-pages.menu.fields.link'), 'link')
                            ->changeFill(static function (mixed $item): ?string {
                                if (data_get($item, 'source_type') !== 'link') {
                                    return null;
                                }

                                $link = (string) data_get($item, 'source_value');

                                return $link === '' ? null : $link;
                            })
                            ->onApply(function (mixed $menu, mixed $value): mixed {
                                $sourceType = (string) data_get($menu, 'source_type');

                                if ($sourceType === 'route') {
                                    return $menu;
                                }

                                if ($sourceType !== 'link') {
                                    data_set($menu, 'source_value', null);

                                    return $menu;
                                }

                                return $this->applySourceValue($menu, $value);
                            })
                            ->showWhen('source_type', 'link')
                            ->hint(__('moonshine-pages::moonshine-pages.menu.hints.link')),

                        BelongsTo::make(__('moonshine-pages::moonshine-pages.menu.fields.page'), 'page', null, MoonShinePagesResources::page())
                            ->searchable()
                            ->nullable()
                            ->showWhen('source_type', 'page'),

                        Switcher::make(__('moonshine-pages::moonshine-pages.menu.fields.prepend_menu_slug'), 'prepend_menu_slug')
                            ->showWhen('source_type', 'page'),

                        Text::make(__('moonshine-pages::moonshine-pages.menu.fields.slug'), 'slug')
                            ->showWhen('source_type', 'page')
                            ->showWhen('prepend_menu_slug', true)
                            ->hint(__('moonshine-pages::moonshine-pages.menu.hints.slug')),

                        Select::make(__('moonshine-pages::moonshine-pages.menu.fields.route'), 'source_value')
                            ->searchable()
                            ->nullable()
                            ->options($this->getRouteOptions())
                            ->onApply(function (mixed $menu, mixed $value): mixed {
                                return (string) data_get($menu, 'source_type') === 'route'
                                    ? $this->applySourceValue($menu, $value)
                                    : $menu;
                            })
                            ->showWhen('source_type', 'route')
                            ->mergeAttribute(
                                'x-on:change',
                                $this->showWhenRefreshDispatchExpression(),
                                ';'
                            ),

                        ...$this->getRouteParameterFields(),

                        Image::make(__('moonshine-pages::moonshine-pages.menu.fields.image'), 'image')
                            ->disk($this->resolveMediaDisk())
                            ->dir($this->resolveMediaDir())
                            ->removable()
                            ->hint(__('moonshine-pages::moonshine-pages.menu.hints.image')),

                        BelongsToMany::make(__('moonshine-pages::moonshine-pages.menu.fields.positions'), 'positions', resource: MoonShinePagesResources::menuPosition())
                            ->asyncOnInit()
                            ->asyncSearch()
                            ->selectMode()
                            ->searchable()
                            ->fields([]),
                    ]),
                ]),
            ]),
        ];
    }

    protected function rules(DataWrapperContract $item): array
    {
        $slugPattern = (string) config(
            'moonshine-pages.route.slug_pattern',
            '^[A-Za-z0-9-_]+(?:/[A-Za-z0-9-_]+)*$'
        );

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            // `#` delimiter (not `/`): the slug pattern may contain `/` for multi-segment slugs.
            'slug' => ['nullable', 'string', 'max:255', 'regex:#'.$slugPattern.'#'],
            'prepend_menu_slug' => ['boolean'],
            'image' => ['nullable', 'file'],
            'is_active' => ['boolean'],
            'source_type' => ['required', 'string', 'in:none,link,page,route'],
            'link' => ['nullable', 'string', 'max:255'],
            'route' => ['nullable', 'string', 'max:255'],
            'source_value' => ['nullable', 'string', 'max:255'],
            'route_params' => ['nullable', 'array'],
        ];

        foreach ($this->extractRouteParameters($this->resolveSelectedRouteName()) as $parameter) {
            $rules["route_params.$parameter"] = ['nullable', 'string', 'max:255'];
        }

        return $rules;
    }

    /**
     * Single writer for the `source_value` column, shared by the "link" text
     * field and the route select. Both fields are applied on every save — the one
     * hidden by `showWhen` included — so each writes only for its own
     * `source_type`, otherwise the later field wipes what the earlier one stored.
     * The column is left untouched when the field is absent from the request
     * (`getRequestValue()` returns false).
     */
    private function applySourceValue(mixed $menu, mixed $value): mixed
    {
        if ($value === false || ! is_scalar($value)) {
            return $menu;
        }

        $sourceValue = trim((string) $value);

        data_set($menu, 'source_value', $sourceValue === '' ? null : $sourceValue);

        return $menu;
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

    private function resolveMediaDisk(): string
    {
        $disk = config('moonshine-pages.media.disk');

        return is_string($disk) && $disk !== ''
            ? $disk
            : (string) config('moonshine.disk', 'public');
    }

    private function resolveMediaDir(): string
    {
        return (string) config('moonshine-pages.media.image_dir', 'menu');
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
