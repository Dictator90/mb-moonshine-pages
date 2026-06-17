<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Make the menu tree per-position: the parent and order of a menu item now live
 * on the menu_menu_position pivot, so the same item can sit under different
 * parents (and in a different order) in each position it belongs to.
 *
 * The menus.parent_id / menus.sort_order columns are kept for backward
 * compatibility; the pivot is the source of truth for the Menu manager and the
 * front-end menu() tree.
 */
return new class extends Migration
{
    public function up(): void
    {
        $menusTable = (string) config('moonshine-pages.tables.menus', 'menus');
        $pivotTable = (string) config('moonshine-pages.tables.menu_menu_position', 'menu_menu_position');

        if (! Schema::hasColumn($pivotTable, 'parent_id')) {
            Schema::table($pivotTable, function (Blueprint $blueprint) use ($pivotTable, $menusTable): void {
                $blueprint->foreignId('parent_id')
                    ->nullable()
                    ->after('menu_position_id')
                    ->constrained($menusTable)
                    ->nullOnDelete();
                $blueprint->integer('sort_order')->default(0)->after('parent_id');

                $blueprint->index(['menu_position_id', 'parent_id'], $pivotTable.'_pos_parent_idx');
            });
        }

        $this->backfill($menusTable, $pivotTable);
    }

    public function down(): void
    {
        $pivotTable = (string) config('moonshine-pages.tables.menu_menu_position', 'menu_menu_position');

        if (Schema::hasColumn($pivotTable, 'parent_id')) {
            Schema::table($pivotTable, function (Blueprint $blueprint) use ($pivotTable): void {
                $blueprint->dropIndex($pivotTable.'_pos_parent_idx');
                $blueprint->dropConstrainedForeignId('parent_id');
                $blueprint->dropColumn('sort_order');
            });
        }
    }

    /**
     * Seed pivot parent_id / sort_order from the existing menus columns so current
     * trees are preserved. A menu's parent is only carried over when that parent
     * is also placed in the same position, otherwise the item becomes a root.
     */
    private function backfill(string $menusTable, string $pivotTable): void
    {
        $pivots = DB::table($pivotTable)->get();

        if ($pivots->isEmpty()) {
            return;
        }

        $menus = DB::table($menusTable)->get(['id', 'parent_id', 'sort_order'])->keyBy('id');

        /** @var array<int, array<int, true>> $inPosition */
        $inPosition = [];
        foreach ($pivots as $pivot) {
            $inPosition[$pivot->menu_position_id][$pivot->menu_id] = true;
        }

        foreach ($pivots as $pivot) {
            $menu = $menus->get($pivot->menu_id);

            if ($menu === null) {
                continue;
            }

            $parentId = $menu->parent_id;
            $resolvedParent = ($parentId !== null && isset($inPosition[$pivot->menu_position_id][$parentId]))
                ? $parentId
                : null;

            DB::table($pivotTable)
                ->where('id', $pivot->id)
                ->update([
                    'parent_id' => $resolvedParent,
                    'sort_order' => (int) ($menu->sort_order ?? 0),
                ]);
        }
    }
};
