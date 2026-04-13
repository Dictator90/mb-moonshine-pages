<?php

declare(strict_types=1);

namespace MB\MoonShine\Support;

final class SlugRouteConstraint
{
    /**
     * @param  list<string>  $reservedSlugs
     */
    public static function buildPattern(string $basePattern, bool $applyReserved, array $reservedSlugs): string
    {
        if (! $applyReserved || $reservedSlugs === []) {
            return $basePattern;
        }

        $normalized = [];

        foreach ($reservedSlugs as $slug) {
            $trimmed = trim($slug, '/');

            if ($trimmed === '') {
                continue;
            }

            $normalized[] = $trimmed;
        }

        if ($normalized === []) {
            return $basePattern;
        }

        if (! preg_match('/^\^(.*)\$$/s', $basePattern, $matches)) {
            return $basePattern;
        }

        $escaped = array_map(
            static fn (string $segment): string => preg_quote($segment, '/'),
            $normalized
        );

        $alternation = implode('|', $escaped);

        return '^(?!(?:'.$alternation.')$)'.$matches[1].'$';
    }

    public static function pattern(): string
    {
        $base = (string) config('moonshine-pages.route.slug_pattern', '^[A-Za-z0-9-_]+$');
        $apply = (bool) config('moonshine-pages.route.apply_reserved_slugs', true);

        /** @var mixed $reservedConfig */
        $reservedConfig = config('moonshine-pages.route.reserved_slugs');

        if (is_array($reservedConfig)) {
            /** @var list<string> $reserved */
            $reserved = array_values(array_filter(
                array_map(static fn (mixed $v): string => is_string($v) ? $v : '', $reservedConfig),
                static fn (string $s): bool => $s !== ''
            ));
        } else {
            $prefix = (string) config('moonshine.prefix', 'admin');

            $reserved = [trim($prefix, '/')];
        }

        return self::buildPattern($base, $apply, $reserved);
    }
}
