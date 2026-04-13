<?php

declare(strict_types=1);

namespace MB\MoonShine\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;

final class PublicPageUrl
{
    /**
     * URL for a published (active) page — used where the link must work on the storefront.
     */
    public static function absolute(mixed $page): ?string
    {
        $row = self::normalizeRow($page);

        if ($row === null) {
            return null;
        }

        if (! (bool) ($row['is_active'] ?? false)) {
            return null;
        }

        return self::routeUrlForRow($row);
    }

    /**
     * URL shown in MoonShine (edit/index/detail) so editors can open or copy the public path
     * even when the page is inactive (storefront may return 404 until activation).
     */
    public static function adminPublicUrl(mixed $page): ?string
    {
        $row = self::normalizeRow($page);

        if ($row === null) {
            return null;
        }

        return self::routeUrlForRow($row);
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function normalizeRow(mixed $page): ?array
    {
        if ($page instanceof Model) {
            $page = $page->getAttributes();
        }

        if (! is_array($page)) {
            return null;
        }

        return $page;
    }

    /**
     * @param  array<string, mixed>  $page
     */
    private static function routeUrlForRow(array $page): ?string
    {
        $slug = $page['slug'] ?? null;

        if (! is_string($slug)) {
            return null;
        }

        $slug = trim($slug);

        if ($slug === '') {
            return null;
        }

        $routeName = (string) config('moonshine-pages.route.name', 'page.show');

        if (! Route::has($routeName)) {
            return null;
        }

        try {
            return route($routeName, ['slug' => $slug], absolute: true);
        } catch (\Throwable) {
            return null;
        }
    }
}
