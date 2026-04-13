<?php

declare(strict_types=1);

namespace MB\MoonShine\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MoonshinePagesDemoSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $pagesTable = (string) config('moonshine-pages.tables.pages', 'pages');
        $positionsTable = (string) config('moonshine-pages.tables.menu_positions', 'menu_positions');

        DB::table($pagesTable)->updateOrInsert(
            ['slug' => 'moonshine-pages-demo'],
            [
                'title' => 'Demo page',
                'is_active' => false,
                'content' => '<p>Demo page from moonshine-pages.</p>',
                'seo_title' => null,
                'seo_description' => null,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        DB::table($positionsTable)->updateOrInsert(
            ['code' => 'main'],
            [
                'name' => 'Header',
                'sort_order' => 10,
                'description' => null,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        DB::table($positionsTable)->updateOrInsert(
            ['code' => 'footer'],
            [
                'name' => 'Footer',
                'sort_order' => 20,
                'description' => null,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );
    }
}
