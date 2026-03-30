<?php

declare(strict_types=1);

namespace MB\MoonShine;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\ServiceProvider;
use MB\MoonShine\MoonShine\Resources\Menu\MenuResource;
use MB\MoonShine\MoonShine\Resources\MenuPosition\MenuPositionResource;
use MB\MoonShine\MoonShine\Resources\Page\PageResource;
use MB\MoonShine\Services\Menu\MenuRepository;
use MoonShine\Contracts\Core\DependencyInjection\CoreContract;
use MoonShine\Contracts\MenuManager\MenuManagerContract;
use MoonShine\Laravel\DependencyInjection\MoonShineConfigurator;
use MoonShine\MenuManager\MenuGroup;
use MoonShine\MenuManager\MenuItem;

class MoonshinePagesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/moonshine-pages.php', 'moonshine-pages');

        $this->app->singleton(MenuRepository::class, function (): MenuRepository {
            /** @var CacheRepository $cache */
            $cache = $this->app->make(CacheRepository::class);

            return new MenuRepository($cache);
        });
    }

    /**
     * @param  CoreContract<MoonShineConfigurator>  $core
     */
    public function boot(CoreContract $core, MenuManagerContract $menu): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'moonshine-pages');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'moonshine-pages');

        if (config('moonshine-pages.register_page_route', true)) {
            $this->loadRoutesFrom(__DIR__.'/../routes/moonshine-pages.php');
        }

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/moonshine-pages.php' => config_path('moonshine-pages.php'),
            ], 'moonshine-pages-config');

            $this->publishesMigrations([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'moonshine-pages-migrations');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/moonshine-pages'),
            ], 'moonshine-pages-views');
        }

        $core->resources([
            PageResource::class,
            MenuPositionResource::class,
            MenuResource::class,
        ]);

        if (config('moonshine-pages.moonshine.register_menu_items', true)) {
            $menu->add([
                MenuGroup::make(__('moonshine-pages::moonshine-pages.menu_group.content'), [
                    MenuItem::make(MenuResource::class, __('moonshine-pages::moonshine-pages.menu.resource_title')),
                    MenuItem::make(MenuPositionResource::class, __('moonshine-pages::moonshine-pages.menu_position.resource_title')),
                    MenuItem::make(PageResource::class, __('moonshine-pages::moonshine-pages.page.resource_title')),
                ]),
            ]);
        }
    }
}
