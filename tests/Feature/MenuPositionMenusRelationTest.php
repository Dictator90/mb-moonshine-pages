<?php

declare(strict_types=1);

namespace MB\MoonShine\Tests\Feature;

use MB\MoonShine\Models\Menu;
use MB\MoonShine\Models\MenuPosition;
use MB\MoonShine\Tests\TestCase;

final class MenuPositionMenusRelationTest extends TestCase
{
    public function test_menu_position_can_attach_menus_via_pivot(): void
    {
        $position = MenuPosition::query()->create([
            'name' => 'Main',
            'code' => 'main',
            'sort_order' => 0,
        ]);

        $menu = Menu::query()->create([
            'name' => 'Home',
            'is_active' => true,
            'source_type' => 'link',
            'source_value' => '/',
            'sort_order' => 0,
        ]);

        $position->menus()->attach($menu->id);

        $this->assertTrue($position->menus()->whereKey($menu->id)->exists());
    }
}
