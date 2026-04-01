<?php

declare(strict_types=1);

namespace MB\MoonShine\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use MB\MoonShine\Support\MoonShinePagesTables;

class MenuPosition extends Model
{
    public function getTable(): string
    {
        return MoonShinePagesTables::menuPositions();
    }

    protected $fillable = [
        'name',
        'code',
        'description',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'int',
        ];
    }

    public function menus(): BelongsToMany
    {
        /** @var class-string<Model> $menuModel */
        $menuModel = (string) config('moonshine-pages.models.menu', Menu::class);

        return $this->belongsToMany($menuModel, MoonShinePagesTables::menuMenuPosition());
    }
}
