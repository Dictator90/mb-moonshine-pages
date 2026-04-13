<?php

declare(strict_types=1);

namespace MB\MoonShine\Tests;

use Illuminate\Foundation\Application;
use MB\MoonShine\MoonshinePagesServiceProvider;
use MoonShine\CKEditor\Providers\CKEditorServiceProvider;
use MoonShine\Laravel\Providers\MoonShineServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    /**
     * @param  Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            MoonShineServiceProvider::class,
            CKEditorServiceProvider::class,
            MoonshinePagesServiceProvider::class,
        ];
    }

    /**
     * @param  Application  $app
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        $app['config']->set('moonshine.use_migrations', false);
        $app['config']->set('moonshine.prefix', 'admin');
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
