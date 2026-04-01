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
        $pagesTable = (string) config('moonshine-pages.tables.pages', 'pages');

        if (! Schema::hasTable($pagesTable)) {
            Schema::create($pagesTable, function (Blueprint $blueprint): void {
                $blueprint->id();
                $blueprint->string('title');
                $blueprint->string('slug')->unique();
                $blueprint->boolean('is_active')->default(true);
                $blueprint->longText('content');
                $blueprint->string('seo_title')->nullable();
                $blueprint->string('seo_description', 500)->nullable();
                $blueprint->timestamps();

                $blueprint->index('is_active');
            });
        }

        DB::table($pagesTable)->updateOrInsert(
            ['slug' => 'moonshine-pages-demo'],
            [
                'title' => 'Демо страница',
                'is_active' => false,
                'content' => '<p>Демо страница пакета moonshine-pages.</p>',
                'seo_title' => null,
                'seo_description' => null,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        $pagesTable = (string) config('moonshine-pages.tables.pages', 'pages');
        Schema::dropIfExists($pagesTable);
    }
};
