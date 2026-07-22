<?php

declare(strict_types=1);

namespace MB\MoonShine;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\ServiceProvider;
use MB\MoonShine\MoonShine\Pages\MenuManagerPage;
use MB\MoonShine\Services\Menu\MenuRepository;
use MB\MoonShine\Support\MoonShinePagesResources;
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

            $this->publishes([
                __DIR__.'/../lang' => lang_path('vendor/moonshine-pages'),
            ], 'moonshine-pages-lang');

            $this->publishes([
                __DIR__.'/../lang' => resource_path('lang/vendor/moonshine-pages'),
            ], 'moonshine-pages-translations');
        }

        $core->resources([
            MoonShinePagesResources::page(),
            MoonShinePagesResources::menuPosition(),
            MoonShinePagesResources::menu(),
        ]);

        $menuManagerEnabled = (bool) config('moonshine-pages.menu_manager.enabled', true);
        /** @var class-string $menuManagerPageClass */
        $menuManagerPageClass = (string) config('moonshine-pages.pages.menu_manager', MenuManagerPage::class);

        if ($menuManagerEnabled) {
            $core->pages([$menuManagerPageClass]);
        }

        if (config('moonshine-pages.moonshine.register_menu_items', true)) {
            $items = [];

            if ($menuManagerEnabled) {
                $items[] = MenuItem::make(
                    $menuManagerPageClass,
                    __('moonshine-pages::moonshine-pages.menu_manager.title'),
                    'bars-3'
                );
            }


            $items = [
                ...$items,
                MenuItem::make(MoonShinePagesResources::menu(), __('moonshine-pages::moonshine-pages.menu.resource_title'), 'list-bullet'),
                MenuItem::make(MoonShinePagesResources::menuPosition(), __('moonshine-pages::moonshine-pages.menu_position.resource_title'), 'view-columns'),
                MenuItem::make(MoonShinePagesResources::page(), __('moonshine-pages::moonshine-pages.page.resource_title'), 'document-text'),
            ];

            $menu->add([
                MenuGroup::make(__('moonshine-pages::moonshine-pages.menu_group.content'), $items, 'rectangle-stack'),
            ]);
        }

        $this->app->booted(function (): void {
            if (! config('moonshine-pages.register_page_route', true)) {
                return;
            }

            $this->loadRoutesFrom(__DIR__.'/../routes/moonshine-pages.php');
        });
    }
}
