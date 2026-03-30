<?php

declare(strict_types=1);

namespace MB\MoonShine\Models;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Routing\Exceptions\UrlGenerationException;
use Illuminate\Support\Facades\Route;
use MB\MoonShine\Services\Menu\MenuRepository;

class Menu extends Model
{
    protected $fillable = [
        'name',
        'menu_position_id',
        'is_active',
        'source_type',
        'source_value',
        'route_params',
        'page_id',
        'parent_id',
        'sort_order',
        'target',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'bool',
            'sort_order' => 'int',
            'route_params' => 'array',
        ];
    }

    public function menuPosition(): BelongsTo
    {
        return $this->belongsTo($this->menuPositionModelClass(), 'menu_position_id');
    }

    public function positions(): BelongsToMany
    {
        return $this->belongsToMany($this->menuPositionModelClass(), 'menu_menu_position');
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo($this->pageModelClass());
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForCode($query, string $code)
    {
        return $query->whereHas('positions', function ($query) use ($code) {
            $query->where('code', $code);
        });
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    public function getUrlAttribute(): string
    {
        $pageRouteName = (string) config('moonshine-pages.route.name', 'page.show');
        $routeParams = $this->normalizeRouteParams($this->route_params);

        return match ($this->source_type) {
            'page' => $this->page && $this->page->is_active
                ? route($pageRouteName, $this->page->slug)
                : '#',
            'route' => $this->source_value && Route::has($this->source_value)
                ? $this->resolveRouteUrl((string) $this->source_value, $routeParams)
                : '#',
            default => $this->source_value ?: '#',
        };
    }

    /**
     * @return array<string, string>
     */
    private function normalizeRouteParams(mixed $params): array
    {
        if ($params instanceof Arrayable) {
            $params = $params->toArray();
        }

        if (! is_array($params)) {
            return [];
        }

        $normalized = [];

        foreach ($params as $key => $value) {
            if (! is_string($key) || trim($key) === '') {
                continue;
            }

            if ($value === null) {
                continue;
            }

            $stringValue = is_scalar($value) ? trim((string) $value) : '';

            if ($stringValue === '') {
                continue;
            }

            $normalized[$key] = $stringValue;
        }

        return $normalized;
    }

    /**
     * @param  array<string, string>  $routeParams
     */
    private function resolveRouteUrl(string $routeName, array $routeParams): string
    {
        try {
            return route($routeName, $routeParams);
        } catch (UrlGenerationException) {
            return '#';
        }
    }

    /**
     * @return class-string<Model>
     */
    private function pageModelClass(): string
    {
        return (string) config('moonshine-pages.models.page', Page::class);
    }

    /**
     * @return class-string<Model>
     */
    private function menuPositionModelClass(): string
    {
        return (string) config('moonshine-pages.models.menu_position', MenuPosition::class);
    }

    protected static function booted(): void
    {
        static::saved(function (self $menu): void {
            $menu->loadMissing('positions');

            foreach ($menu->positions as $position) {
                app(MenuRepository::class)->forget($position->code);
            }
        });

        static::deleted(function (self $menu): void {
            $menu->loadMissing('positions');

            foreach ($menu->positions as $position) {
                app(MenuRepository::class)->forget($position->code);
            }
        });
    }
}
