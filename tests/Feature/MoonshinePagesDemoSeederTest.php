<?php

declare(strict_types=1);

namespace MB\MoonShine\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use MB\MoonShine\Database\Seeders\MoonshinePagesDemoSeeder;
use MB\MoonShine\Tests\TestCase;

final class MoonshinePagesDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_inserts_demo_page_and_menu_positions(): void
    {
        $this->seed(MoonshinePagesDemoSeeder::class);

        $this->assertDatabaseHas('pages', [
            'slug' => 'moonshine-pages-demo',
            'title' => 'Demo page',
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('menu_positions', [
            'code' => 'main',
            'name' => 'Header',
        ]);

        $this->assertDatabaseHas('menu_positions', [
            'code' => 'footer',
            'name' => 'Footer',
        ]);
    }
}
