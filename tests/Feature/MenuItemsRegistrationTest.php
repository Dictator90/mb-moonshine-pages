<?php

declare(strict_types=1);

namespace MB\MoonShine\Tests\Feature;

use MB\MoonShine\Tests\TestCase;

final class MenuItemsRegistrationTest extends TestCase
{
    /**
     * Regression: the 1.1.1 refactor moved resource resolution to
     * {@see \MB\MoonShine\Support\MoonShinePagesResources} but left the menu-items
     * block in the service provider referencing the removed local variables
     * ($menuResourceClass, ...), so `boot()` raised "Undefined variable" whenever
     * `register_menu_items` was enabled and crashed anything that boots the app
     * (e.g. `php artisan wayfinder:generate`).
     */
    public function test_menu_items_use_resource_resolver_not_removed_variables(): void
    {
        $contents = file_get_contents(__DIR__.'/../../src/MoonshinePagesServiceProvider.php');

        $this->assertIsString($contents);

        $this->assertStringContainsString('MoonShinePagesResources::menu()', $contents);
        $this->assertStringContainsString('MoonShinePagesResources::menuPosition()', $contents);
        $this->assertStringContainsString('MoonShinePagesResources::page()', $contents);

        $this->assertStringNotContainsString('$menuResourceClass', $contents);
        $this->assertStringNotContainsString('$menuPositionResourceClass', $contents);
        $this->assertStringNotContainsString('$pageResourceClass', $contents);
    }
}
