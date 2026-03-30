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
        if (! Schema::hasTable('pages')) {
            Schema::create('pages', function (Blueprint $table): void {
                $table->id();
                $table->string('title');
                $table->string('slug')->unique();
                $table->boolean('is_active')->default(true);
                $table->longText('content');
                $table->string('seo_title')->nullable();
                $table->string('seo_description', 500)->nullable();
                $table->timestamps();

                $table->index('is_active');
            });
        }

        DB::table('pages')->updateOrInsert(
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
        Schema::dropIfExists('pages');
    }
};
