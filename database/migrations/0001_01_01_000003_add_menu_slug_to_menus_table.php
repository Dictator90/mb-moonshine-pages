<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $menusTable = (string) config('moonshine-pages.tables.menus', 'menus');

        if (! Schema::hasTable($menusTable)) {
            return;
        }

        Schema::table($menusTable, function (Blueprint $blueprint) use ($menusTable): void {
            if (! Schema::hasColumn($menusTable, 'slug')) {
                $blueprint->string('slug')->nullable()->after('name');
            }

            if (! Schema::hasColumn($menusTable, 'prepend_menu_slug')) {
                $blueprint->boolean('prepend_menu_slug')->default(false)->after('slug');
            }
        });
    }

    public function down(): void
    {
        $menusTable = (string) config('moonshine-pages.tables.menus', 'menus');

        if (! Schema::hasTable($menusTable)) {
            return;
        }

        Schema::table($menusTable, function (Blueprint $blueprint) use ($menusTable): void {
            if (Schema::hasColumn($menusTable, 'prepend_menu_slug')) {
                $blueprint->dropColumn('prepend_menu_slug');
            }

            if (Schema::hasColumn($menusTable, 'slug')) {
                $blueprint->dropColumn('slug');
            }
        });
    }
};
