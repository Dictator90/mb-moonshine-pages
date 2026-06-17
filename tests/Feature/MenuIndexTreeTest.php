<?php

declare(strict_types=1);

namespace MB\MoonShine\Tests\Feature;

use Illuminate\Support\Collection;
use MB\MoonShine\Models\Menu;
use MB\MoonShine\MoonShine\Resources\Menu\MenuResource;
use MB\MoonShine\Tests\TestCase;

final class MenuIndexTreeTest extends TestCase
{
    private function menu(int $id, ?int $parentId, string $name, int $sortOrder = 0): Menu
    {
        $menu = new Menu();
        $menu->forceFill([
            'id' => $id,
            'parent_id' => $parentId,
            'name' => $name,
            'sort_order' => $sortOrder,
        ]);

        return $menu;
    }

    public function test_index_tree_disabled_by_default(): void
    {
        $this->assertFalse(MenuResource::indexTreeEnabled());

        config(['moonshine-pages.menu.index_tree' => true]);

        $this->assertTrue(MenuResource::indexTreeEnabled());
    }

    public function test_order_as_tree_produces_depth_first_order_with_depth_and_indentation(): void
    {
        $items = new Collection([
            $this->menu(1, null, 'Root A', 10),
            $this->menu(2, 1, 'Child A1', 10),
            $this->menu(3, 2, 'Grandchild A1a', 10),
            $this->menu(4, null, 'Root B', 5),
            $this->menu(5, 999, 'Orphan', 20), // parent not in set -> treated as root
        ]);

        $ordered = MenuResource::orderAsTree($items);

        // Roots sorted by sort_order (Root B = 5 before Root A = 10), then depth-first;
        // the orphan (missing parent) becomes a root and sorts last by sort_order.
        $this->assertSame([4, 1, 2, 3, 5], $ordered->map(fn (Menu $m) => $m->getAttribute('id'))->all());

        $byId = $ordered->keyBy(fn (Menu $m) => $m->getAttribute('id'));

        $this->assertSame(0, $byId[4]->getAttribute('tree_depth'));
        $this->assertSame(0, $byId[1]->getAttribute('tree_depth'));
        $this->assertSame(1, $byId[2]->getAttribute('tree_depth'));
        $this->assertSame(2, $byId[3]->getAttribute('tree_depth'));
        $this->assertSame(0, $byId[5]->getAttribute('tree_depth'));

        $this->assertSame('Root A', $byId[1]->getAttribute('tree_name'));
        $this->assertSame('— Child A1', $byId[2]->getAttribute('tree_name'));
        $this->assertSame('— — Grandchild A1a', $byId[3]->getAttribute('tree_name'));
    }

    public function test_order_as_tree_keeps_all_items_when_a_cycle_exists(): void
    {
        // 1 -> 2 -> 1 forms a cycle; neither is reachable from a real root.
        $items = new Collection([
            $this->menu(1, 2, 'Node 1'),
            $this->menu(2, 1, 'Node 2'),
        ]);

        $ordered = MenuResource::orderAsTree($items);

        $this->assertCount(2, $ordered);
    }

    public function test_tree_options_use_breadcrumb_path_and_depth_first_order(): void
    {
        /** @var class-string<\Illuminate\Database\Eloquent\Model> $menuModel */
        $menuModel = config('moonshine-pages.models.menu');

        $root = $menuModel::query()->create([
            'name' => 'Root', 'is_active' => true, 'sort_order' => 10, 'source_type' => 'none',
        ]);
        $child = $menuModel::query()->create([
            'name' => 'Child', 'parent_id' => $root->getKey(), 'is_active' => true, 'sort_order' => 10, 'source_type' => 'none',
        ]);
        $grandchild = $menuModel::query()->create([
            'name' => 'Sub', 'parent_id' => $child->getKey(), 'is_active' => true, 'sort_order' => 10, 'source_type' => 'none',
        ]);

        $options = MenuResource::treeOptions();

        $this->assertSame('Root', $options[$root->getKey()]);
        $this->assertSame('Root / Child', $options[$child->getKey()]);
        $this->assertSame('Root / Child / Sub', $options[$grandchild->getKey()]);

        // Depth-first: the root key comes before its child key.
        $keys = array_keys($options);
        $this->assertLessThan(
            array_search($child->getKey(), $keys, true),
            array_search($root->getKey(), $keys, true),
        );
    }
}
