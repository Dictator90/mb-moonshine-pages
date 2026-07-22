<?php

declare(strict_types=1);

namespace MB\MoonShine\Tests\Feature;

use MB\MoonShine\Tests\TestCase;

final class MenuPositionsFieldTest extends TestCase
{
    public function test_menu_positions_field_uses_async_search_select(): void
    {
        $contents = file_get_contents(__DIR__.'/../../src/MoonShine/Resources/Menu/Pages/MenuFormPage.php');

        $this->assertIsString($contents);
        $this->assertStringContainsString('menu.fields.positions', $contents);
        $this->assertStringContainsString('resource: MoonShinePagesResources::menuPosition()', $contents);
        $this->assertStringContainsString('->asyncSearch()', $contents);
        $this->assertStringContainsString('->fields([])', $contents);
        // The positions field preloads its options on init (async-on-init).
        $this->assertStringContainsString('->asyncOnInit()', $contents);
    }

    public function test_menu_position_tab_uses_select_mode_for_menus(): void
    {
        $contents = file_get_contents(__DIR__.'/../../src/MoonShine/Resources/MenuPosition/Pages/MenuPositionFormPage.php');

        $this->assertIsString($contents);
        $this->assertStringContainsString("menu_position.tabs.menus", $contents);
        $this->assertStringContainsString("->asyncSearch()", $contents);
        $this->assertStringContainsString("->selectMode()", $contents);
        $this->assertStringContainsString("->searchable()", $contents);
        $this->assertStringContainsString("->fields([])", $contents);
    }
}
