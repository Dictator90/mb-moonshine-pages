<?php

declare(strict_types=1);

namespace MB\MoonShine\Tests\Feature;

use MB\MoonShine\Models\Menu;
use MB\MoonShine\Models\MenuPosition;
use MB\MoonShine\Models\Page;
use MB\MoonShine\Tests\TestCase;

final class MenuSlugPrefixTest extends TestCase
{
    public function test_menu_url_prepends_slug_when_enabled(): void
    {
        $page = Page::query()->create([
            'title' => 'About',
            'slug' => 'about',
            'content' => '<p>About</p>',
            'is_active' => true,
        ]);

        $menu = Menu::query()->create([
            'name' => 'About in news',
            'slug' => 'news',
            'prepend_menu_slug' => true,
            'is_active' => true,
            'source_type' => 'page',
            'page_id' => $page->id,
            'sort_order' => 0,
        ]);

        $this->assertStringEndsWith('/news/about', $menu->url);
    }

    public function test_menu_url_ignores_slug_when_toggle_disabled(): void
    {
        $page = Page::query()->create([
            'title' => 'About',
            'slug' => 'about',
            'content' => '<p>About</p>',
            'is_active' => true,
        ]);

        $menu = Menu::query()->create([
            'name' => 'About',
            'slug' => 'news',
            'prepend_menu_slug' => false,
            'is_active' => true,
            'source_type' => 'page',
            'page_id' => $page->id,
            'sort_order' => 0,
        ]);

        $this->assertStringEndsWith('/about', $menu->url);
        $this->assertStringNotContainsString('/news/about', $menu->url);
    }

    public function test_cached_menu_tree_reflects_prefixed_url(): void
    {
        $page = Page::query()->create([
            'title' => 'About',
            'slug' => 'about',
            'content' => '<p>About</p>',
            'is_active' => true,
        ]);

        $position = MenuPosition::query()->create([
            'name' => 'Main',
            'code' => 'main',
            'sort_order' => 0,
        ]);

        $menu = Menu::query()->create([
            'name' => 'About in news',
            'slug' => 'news',
            'prepend_menu_slug' => true,
            'is_active' => true,
            'source_type' => 'page',
            'page_id' => $page->id,
            'sort_order' => 0,
        ]);

        $menu->positions()->attach($position->id);

        $tree = menu('main');

        $this->assertNotEmpty($tree);
        $this->assertStringEndsWith('/news/about', $tree[0]['url']);
    }
}
