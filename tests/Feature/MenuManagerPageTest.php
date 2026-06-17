<?php

declare(strict_types=1);

namespace MB\MoonShine\Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use MB\MoonShine\Models\Menu;
use MB\MoonShine\Models\MenuPosition;
use MB\MoonShine\Models\Page;
use MB\MoonShine\MoonShine\Pages\MenuManagerPage;
use MB\MoonShine\Services\Menu\MenuManagerService;
use MB\MoonShine\Services\Menu\MenuRepository;
use MB\MoonShine\Support\MoonShinePagesTables;
use MB\MoonShine\Tests\TestCase;

final class MenuManagerPageTest extends TestCase
{
    private function position(string $code, string $name, int $sort = 0): MenuPosition
    {
        return MenuPosition::query()->create([
            'name' => $name,
            'code' => $code,
            'sort_order' => $sort,
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function menu(array $attributes): Menu
    {
        return Menu::query()->create(array_merge([
            'name' => 'Item',
            'is_active' => true,
            'source_type' => 'link',
            'source_value' => '/',
            'sort_order' => 0,
        ], $attributes));
    }

    /** Place a menu in a position with a per-position parent + order (the pivot). */
    private function place(Menu $menu, MenuPosition $position, ?int $parentId = null, int $sort = 0): void
    {
        $menu->positions()->attach($position->id, ['parent_id' => $parentId, 'sort_order' => $sort]);
    }

    private function pivotParent(int $menuId, int $positionId): ?int
    {
        $value = DB::table(MoonShinePagesTables::menuMenuPosition())
            ->where('menu_id', $menuId)
            ->where('menu_position_id', $positionId)
            ->value('parent_id');

        return $value !== null ? (int) $value : null;
    }

    private function pivotSort(int $menuId, int $positionId): ?int
    {
        $value = DB::table(MoonShinePagesTables::menuMenuPosition())
            ->where('menu_id', $menuId)
            ->where('menu_position_id', $positionId)
            ->value('sort_order');

        return $value !== null ? (int) $value : null;
    }

    /**
     * @param  list<array<string,mixed>>  $items
     * @return array<string,mixed>|null
     */
    private function findNode(array $items, string $name): ?array
    {
        foreach ($items as $item) {
            if ($item['name'] === $name) {
                return $item;
            }

            $found = $this->findNode($item['children'], $name);

            if ($found !== null) {
                return $found;
            }
        }

        return null;
    }

    /**
     * @param  list<array{id:int,code:string,name:string,items:list<array<string,mixed>>}>  $positions
     * @return list<array<string,mixed>>
     */
    private function items(array $positions, string $code): array
    {
        foreach ($positions as $position) {
            if ($position['code'] === $code) {
                return $position['items'];
            }
        }

        return [];
    }

    private function page(): MenuManagerPage
    {
        return app(MenuManagerPage::class);
    }

    /**
     * @param  array<string, mixed>  $input
     */
    private function request(array $input): Request
    {
        return Request::create('/', 'POST', $input, server: [
            'HTTP_REFERER' => 'http://localhost/admin/page/menu-manager-page',
        ]);
    }

    public function test_page_is_registered_and_titled(): void
    {
        $this->assertSame(MenuManagerPage::class, config('moonshine-pages.pages.menu_manager'));
        $this->assertSame('Menu management', $this->page()->getTitle());
    }

    public function test_service_builds_position_tree_with_hidden_items_and_presentation(): void
    {
        $header = $this->position('header', 'Header', 0);
        $footer = $this->position('footer', 'Footer', 1);

        $page = Page::query()->create(['title' => 'About', 'slug' => 'about', 'is_active' => true, 'content' => '<p>About</p>']);

        $parent = $this->menu(['name' => 'About', 'source_type' => 'page', 'page_id' => $page->id]);
        $child = $this->menu(['name' => 'Team', 'source_type' => 'route', 'source_value' => 'team.index', 'is_active' => false]);

        $this->place($parent, $header);
        $this->place($child, $header, $parent->id);

        $positions = app(MenuManagerService::class)->positions();

        // Ordered by sort_order: header then footer.
        $this->assertSame(['header', 'footer'], array_column($positions, 'code'));

        $headerItems = $positions[0]['items'];
        $this->assertCount(1, $headerItems);

        $root = $headerItems[0];
        $this->assertSame('About', $root['name']);
        $this->assertSame('page', $root['source_type']);
        $this->assertSame('green', $root['source_color']);
        $this->assertSame('Page', $root['source_label']);
        $this->assertSame('/about', $root['target_display']);

        // Hidden child is still present (admin tree, unlike the cached frontend tree).
        $this->assertCount(1, $root['children']);
        $hiddenChild = $root['children'][0];
        $this->assertSame('Team', $hiddenChild['name']);
        $this->assertFalse($hiddenChild['is_active']);
        $this->assertSame('yellow', $hiddenChild['source_color']);
        $this->assertSame('team.index', $hiddenChild['target_display']);

        // Footer has no items.
        $this->assertSame([], $positions[1]['items']);
    }

    public function test_item_can_have_different_parents_in_different_positions(): void
    {
        $header = $this->position('header', 'Header', 0);
        $footer = $this->position('footer', 'Footer', 1);

        $useful = $this->menu(['name' => 'Useful']);
        $company = $this->menu(['name' => 'Company']);
        $reviews = $this->menu(['name' => 'Reviews']);

        // In HEADER: Useful → Reviews
        $this->place($useful, $header);
        $this->place($reviews, $header, $useful->id);

        // In FOOTER: Company → Reviews (same Reviews item, different parent)
        $this->place($company, $footer);
        $this->place($reviews, $footer, $company->id);

        $positions = app(MenuManagerService::class)->positions();

        $usefulNode = $this->findNode($this->items($positions, 'header'), 'Useful');
        $this->assertNotNull($usefulNode);
        $this->assertSame(['Reviews'], array_column($usefulNode['children'], 'name'));

        $companyNode = $this->findNode($this->items($positions, 'footer'), 'Company');
        $this->assertNotNull($companyNode);
        $this->assertSame(['Reviews'], array_column($companyNode['children'], 'name'));

        // The same Reviews item is a child of two different parents per position.
        $this->assertSame($useful->id, $this->pivotParent($reviews->id, $header->id));
        $this->assertSame($company->id, $this->pivotParent($reviews->id, $footer->id));
    }

    public function test_available_for_flags_items_already_in_position(): void
    {
        $header = $this->position('header', 'Header');

        $inside = $this->menu(['name' => 'Inside']);
        $outside = $this->menu(['name' => 'Outside']);
        $this->place($inside, $header);

        $available = app(MenuManagerService::class)->availableFor($header->id);

        $byName = collect($available)->keyBy('name');
        $this->assertTrue($byName['Inside']['already']);
        $this->assertFalse($byName['Outside']['already']);
    }

    public function test_toggle_active_flips_flag_and_invalidates_cache(): void
    {
        $header = $this->position('header', 'Header');
        $menu = $this->menu(['name' => 'Home', 'is_active' => true]);
        $this->place($menu, $header);

        // Prime the cached frontend tree.
        $this->assertCount(1, app(MenuRepository::class)->getTree('header'));

        $this->page()->toggleActive($this->request(['id' => $menu->id, 'position' => $header->id]));

        $this->assertFalse($menu->fresh()->is_active);
        // Hidden item drops out of the cached active-only tree => cache was invalidated.
        $this->assertCount(0, app(MenuRepository::class)->getTree('header'));
    }

    public function test_toggle_collapse_persists_expanded_state_in_session(): void
    {
        $header = $this->position('header', 'Header');
        $menu = $this->menu(['name' => 'Parent']);
        $this->place($menu, $header);

        // Expanding records the id in the session.
        $this->page()->toggleCollapse($this->request(['id' => $menu->id, 'open' => true]));
        $this->assertContains($menu->id, session()->get('moonshine-pages.menu_manager.expanded', []));

        // Collapsing removes it again.
        $this->page()->toggleCollapse($this->request(['id' => $menu->id, 'open' => false]));
        $this->assertNotContains($menu->id, session()->get('moonshine-pages.menu_manager.expanded', []));
    }

    public function test_duplicate_clones_item_and_keeps_placements(): void
    {
        $header = $this->position('header', 'Header');
        $parent = $this->menu(['name' => 'Parent']);
        $menu = $this->menu(['name' => 'Home']);
        $this->place($parent, $header);
        $this->place($menu, $header, $parent->id, 3);

        $this->page()->duplicate($this->request(['id' => $menu->id, 'position' => $header->id]));

        $copy = Menu::query()->where('name', 'Home (copy)')->first();
        $this->assertNotNull($copy);
        // The copy keeps the same per-position parent + order.
        $this->assertSame($parent->id, $this->pivotParent($copy->id, $header->id));
        $this->assertSame(3, $this->pivotSort($copy->id, $header->id));
    }

    public function test_detach_removes_placement_and_promotes_its_children(): void
    {
        $header = $this->position('header', 'Header');
        $parent = $this->menu(['name' => 'Parent']);
        $child = $this->menu(['name' => 'Child']);
        $this->place($parent, $header);
        $this->place($child, $header, $parent->id);

        $this->page()->detach($this->request(['id' => $parent->id, 'position' => $header->id]));

        $this->assertFalse($parent->positions()->whereKey($header->id)->exists());
        $this->assertNotNull($parent->fresh()); // the menu itself survives
        // The child stays in the position, promoted to root (parent had no parent there).
        $this->assertNull($this->pivotParent($child->id, $header->id));
    }

    public function test_destroy_deletes_item_and_promotes_children_per_position(): void
    {
        $header = $this->position('header', 'Header');
        $parent = $this->menu(['name' => 'Parent']);
        $child = $this->menu(['name' => 'Child']);
        $this->place($parent, $header);
        $this->place($child, $header, $parent->id);

        $this->page()->destroy($this->request(['id' => $parent->id, 'position' => $header->id]));

        $this->assertNull(Menu::query()->find($parent->id));
        // Child promoted to root in the position (parent had no parent there).
        $this->assertNull($this->pivotParent($child->id, $header->id));
    }

    public function test_attach_adds_items_to_position_without_duplicates(): void
    {
        $header = $this->position('header', 'Header');
        $a = $this->menu(['name' => 'A']);
        $b = $this->menu(['name' => 'B']);
        $this->place($b, $header); // already attached

        $this->page()->attach($this->request(['position' => $header->id, 'ids' => [$a->id, $b->id]]));

        $this->assertTrue($a->positions()->whereKey($header->id)->exists());
        $this->assertSame(1, $b->positions()->whereKey($header->id)->count());
    }

    public function test_reorder_sets_pivot_parent_and_order_for_position(): void
    {
        $header = $this->position('header', 'Header');
        $parent = $this->menu(['name' => 'Parent']);
        $one = $this->menu(['name' => 'One']);
        $two = $this->menu(['name' => 'Two']);

        $this->place($parent, $header, null, 0);
        $this->place($one, $header, null, 1);
        $this->place($two, $header, null, 2);

        // Move $two under $parent and order [$two] inside it.
        $this->page()->reorder($this->request([
            'id' => $two->id,
            'position' => $header->id,
            'parent' => (string) $parent->id,
            'data' => (string) $two->id,
        ]));

        $this->assertSame($parent->id, $this->pivotParent($two->id, $header->id));
        $this->assertSame(0, $this->pivotSort($two->id, $header->id));
        // The menu-level column is NOT used as the tree source anymore.
    }

    public function test_reorder_only_touches_the_target_position(): void
    {
        $header = $this->position('header', 'Header');
        $footer = $this->position('footer', 'Footer');
        $parent = $this->menu(['name' => 'Parent']);
        $item = $this->menu(['name' => 'Item']);

        $this->place($parent, $header);
        $this->place($item, $header);
        $this->place($item, $footer); // also in footer, as a root

        $this->page()->reorder($this->request([
            'id' => $item->id,
            'position' => $header->id,
            'parent' => (string) $parent->id,
            'data' => (string) $item->id,
        ]));

        $this->assertSame($parent->id, $this->pivotParent($item->id, $header->id));
        // Footer placement is untouched.
        $this->assertNull($this->pivotParent($item->id, $footer->id));
    }

    public function test_reorder_ignores_source_list_event_when_moved_id_absent(): void
    {
        $header = $this->position('header', 'Header');
        $a = $this->menu(['name' => 'A']);
        $this->place($a, $header);

        // Simulate the source-list onSort of a cross-list move: moved id not in data.
        $this->page()->reorder($this->request([
            'id' => $a->id,
            'position' => $header->id,
            'parent' => '',
            'data' => '999', // some other ids, not $a
        ]));

        // Parent unchanged because the event was ignored.
        $this->assertNull($this->pivotParent($a->id, $header->id));
    }

    public function test_reorder_rejects_cycle(): void
    {
        $header = $this->position('header', 'Header');
        $parent = $this->menu(['name' => 'Parent']);
        $child = $this->menu(['name' => 'Child']);
        $this->place($parent, $header);
        $this->place($child, $header, $parent->id);

        // Attempt to make $parent a child of its own child $child.
        $this->page()->reorder($this->request([
            'id' => $parent->id,
            'position' => $header->id,
            'parent' => (string) $child->id,
            'data' => (string) $parent->id,
        ]));

        $this->assertNull($this->pivotParent($parent->id, $header->id)); // unchanged
    }

    public function test_views_render_without_errors(): void
    {
        $node = static function (array $attrs) use (&$node): array {
            return array_merge([
                'id' => 1,
                'name' => 'Item',
                'is_active' => true,
                'icon_url' => null,
                'source_type' => 'link',
                'source_label' => 'Link',
                'source_color' => 'blue',
                'target_display' => '/',
                'edit_url' => '#',
                'children' => [],
            ], $attrs);
        };

        $data = [
            'title' => 'Menu management',
            'createUrl' => '#',
            'positions' => [
                [
                    'position' => [
                        'id' => 1,
                        'code' => 'header',
                        'name' => 'Header',
                        'items' => [
                            $node([
                                'id' => 1,
                                'name' => 'About',
                                'source_type' => 'page',
                                'source_label' => 'Page',
                                'source_color' => 'green',
                                'target_display' => '/about',
                                'children' => [
                                    $node([
                                        'id' => 2,
                                        'name' => 'Team',
                                        'is_active' => false,
                                        'icon_url' => 'https://example.test/icon.png',
                                        'source_type' => 'route',
                                        'source_label' => 'Route',
                                        'source_color' => 'yellow',
                                        'target_display' => 'team.index',
                                    ]),
                                ],
                            ]),
                        ],
                    ],
                    'available' => [
                        ['id' => 3, 'name' => 'Blog', 'source_type' => 'none', 'source_label' => 'No source', 'source_color' => 'gray', 'is_active' => false, 'already' => false],
                    ],
                    'endpoints' => [
                        'toggleActive' => '#', 'toggleCollapse' => '#', 'duplicate' => '#',
                        'detach' => '#', 'destroy' => '#', 'attach' => '#', 'reorder' => '#',
                    ],
                    'createUrl' => '#',
                ],
            ],
        ];

        $html = view('moonshine-pages::menu-manager.index', $data)->render();

        $this->assertStringContainsString('Header', $html);
        $this->assertStringContainsString('About', $html);
        $this->assertStringContainsString('Team', $html);
        // Reorder URL carries the position id for per-position pivot updates.
        $this->assertStringContainsString("sortable('#&position=1', 'ms-pos-1')", $html);
    }
}
