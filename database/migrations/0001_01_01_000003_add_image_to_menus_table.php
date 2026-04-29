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

        if (Schema::hasColumn($menusTable, 'image')) {
            return;
        }

        Schema::table($menusTable, function (Blueprint $blueprint): void {
            $blueprint->string('image')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        $menusTable = (string) config('moonshine-pages.tables.menus', 'menus');

        if (! Schema::hasTable($menusTable) || ! Schema::hasColumn($menusTable, 'image')) {
            return;
        }

        Schema::table($menusTable, function (Blueprint $blueprint): void {
            $blueprint->dropColumn('image');
        });
    }
};
