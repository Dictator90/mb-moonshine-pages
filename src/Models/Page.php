<?php

declare(strict_types=1);

namespace MB\MoonShine\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MB\MoonShine\Support\MoonShinePagesTables;

class Page extends Model
{
    public function getTable(): string
    {
        return MoonShinePagesTables::pages();
    }

    protected $fillable = [
        'title',
        'slug',
        'is_active',
        'content',
        'seo_title',
        'seo_description',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'bool',
        ];
    }

    public function menus(): HasMany
    {
        /** @var class-string<Model> $menuModel */
        $menuModel = (string) config('moonshine-pages.models.menu', Menu::class);

        return $this->hasMany($menuModel);
    }
}
