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
        if (! Schema::hasTable('menu_positions')) {
            Schema::create('menu_positions', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('code')->unique();
                $table->integer('sort_order')->default(0);
                $table->text('description')->nullable();
                $table->timestamps();

                $table->index('sort_order');
            });
        }

        if (! Schema::hasTable('menus')) {
            Schema::create('menus', function (Blueprint $table): void {
                $table->id();
                $table->boolean('is_active')->default(true);
                $table->string('name');
                $table->integer('sort_order')->default(0);
                $table->string('source_type', 50)->default('link');
                $table->string('source_value')->nullable();
                $table->json('route_params')->nullable();
                $table->foreignId('page_id')->nullable()->constrained('pages')->nullOnDelete();
                $table->foreignId('parent_id')->nullable()->constrained('menus')->nullOnDelete();
                $table->foreignId('menu_position_id')->nullable()->constrained('menu_positions')->nullOnDelete();
                $table->string('target', 20)->nullable();
                $table->timestamps();

                $table->index('parent_id');
                $table->index('page_id');
                $table->index('sort_order');
                $table->index('source_type');
            });
        }

        if (! Schema::hasTable('menu_menu_position')) {
            Schema::create('menu_menu_position', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('menu_id')->constrained('menus')->cascadeOnDelete();
                $table->foreignId('menu_position_id')->constrained('menu_positions')->cascadeOnDelete();
                $table->unique(['menu_id', 'menu_position_id']);
            });
        }

        $now = now();

        DB::table('menu_positions')->updateOrInsert(
            ['code' => 'main'],
            [
                'name' => 'Header',
                'sort_order' => 10,
                'description' => null,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        DB::table('menu_positions')->updateOrInsert(
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
        Schema::dropIfExists('menu_menu_position');
        Schema::dropIfExists('menus');
        Schema::dropIfExists('menu_positions');
    }
};
