<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $positionsTable = (string) config('moonshine-pages.tables.menu_positions', 'menu_positions');
        $menusTable = (string) config('moonshine-pages.tables.menus', 'menus');
        $pivotTable = (string) config('moonshine-pages.tables.menu_menu_position', 'menu_menu_position');
        $pagesTable = (string) config('moonshine-pages.tables.pages', 'pages');

        if (! Schema::hasTable($positionsTable)) {
            Schema::create($positionsTable, function (Blueprint $blueprint): void {
                $blueprint->id();
                $blueprint->string('name');
                $blueprint->string('code')->unique();
                $blueprint->integer('sort_order')->default(0);
                $blueprint->text('description')->nullable();
                $blueprint->timestamps();

                $blueprint->index('sort_order');
            });
        }

        if (! Schema::hasTable($menusTable)) {
            Schema::create($menusTable, function (Blueprint $blueprint) use ($pagesTable, $menusTable, $positionsTable): void {
                $blueprint->id();
                $blueprint->boolean('is_active')->default(true);
                $blueprint->string('name');
                $blueprint->integer('sort_order')->default(0);
                $blueprint->string('source_type', 50)->default('link');
                $blueprint->string('source_value')->nullable();
                $blueprint->json('route_params')->nullable();
                $blueprint->foreignId('page_id')->nullable()->constrained($pagesTable)->nullOnDelete();
                $blueprint->foreignId('parent_id')->nullable()->constrained($menusTable)->nullOnDelete();
                $blueprint->foreignId('menu_position_id')->nullable()->constrained($positionsTable)->nullOnDelete();
                $blueprint->string('target', 20)->nullable();
                $blueprint->timestamps();

                $blueprint->index('parent_id');
                $blueprint->index('page_id');
                $blueprint->index('sort_order');
                $blueprint->index('source_type');
            });
        }

        if (! Schema::hasTable($pivotTable)) {
            Schema::create($pivotTable, function (Blueprint $blueprint) use ($menusTable, $positionsTable): void {
                $blueprint->id();
                $blueprint->foreignId('menu_id')->constrained($menusTable)->cascadeOnDelete();
                $blueprint->foreignId('menu_position_id')->constrained($positionsTable)->cascadeOnDelete();
                $blueprint->unique(['menu_id', 'menu_position_id']);
            });
        }

        $now = now();

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

    public function down(): void
    {
        $positionsTable = (string) config('moonshine-pages.tables.menu_positions', 'menu_positions');
        $menusTable = (string) config('moonshine-pages.tables.menus', 'menus');
        $pivotTable = (string) config('moonshine-pages.tables.menu_menu_position', 'menu_menu_position');

        Schema::dropIfExists($pivotTable);
        Schema::dropIfExists($menusTable);
        Schema::dropIfExists($positionsTable);
    }
};
