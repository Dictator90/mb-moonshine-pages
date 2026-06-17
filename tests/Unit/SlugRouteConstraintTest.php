<?php

declare(strict_types=1);

namespace MB\MoonShine\Tests\Unit;

use MB\MoonShine\Support\SlugRouteConstraint;
use PHPUnit\Framework\TestCase;

final class SlugRouteConstraintTest extends TestCase
{
    public function test_build_pattern_inserts_negative_lookahead_for_reserved_slugs(): void
    {
        $pattern = SlugRouteConstraint::buildPattern('^[A-Za-z0-9-_]+$', true, ['admin']);

        $this->assertSame('^(?!(?:admin)(?:/|$))[A-Za-z0-9-_]+$', $pattern);
    }

    public function test_build_pattern_supports_multiple_reserved_segments(): void
    {
        $pattern = SlugRouteConstraint::buildPattern('^[A-Za-z0-9-_]+$', true, ['admin', 'up']);

        $this->assertSame('^(?!(?:admin|up)(?:/|$))[A-Za-z0-9-_]+$', $pattern);
    }

    public function test_multi_segment_pattern_matches_nested_slug_but_not_reserved_paths(): void
    {
        $base = '^[A-Za-z0-9-_]+(?:/[A-Za-z0-9-_]+)*$';

        $pattern = SlugRouteConstraint::buildPattern($base, true, ['admin']);

        $this->assertSame('^(?!(?:admin)(?:/|$))[A-Za-z0-9-_]+(?:/[A-Za-z0-9-_]+)*$', $pattern);

        $regex = '~'.$pattern.'~';

        // Nested page paths are accepted.
        $this->assertSame(1, preg_match($regex, 'catalog/feature/common'));
        $this->assertSame(1, preg_match($regex, 'about'));

        // Reserved value is blocked both as an exact slug and as a path prefix.
        $this->assertSame(0, preg_match($regex, 'admin'));
        $this->assertSame(0, preg_match($regex, 'admin/users'));

        // Malformed paths are rejected.
        $this->assertSame(0, preg_match($regex, '/leading'));
        $this->assertSame(0, preg_match($regex, 'trailing/'));
        $this->assertSame(0, preg_match($regex, 'double//slash'));
    }

    public function test_build_pattern_returns_base_when_disabled(): void
    {
        $base = '^[A-Za-z0-9-_]+$';

        $this->assertSame($base, SlugRouteConstraint::buildPattern($base, false, ['admin']));
    }

    public function test_build_pattern_returns_base_when_reserved_list_is_empty(): void
    {
        $base = '^[A-Za-z0-9-_]+$';

        $this->assertSame($base, SlugRouteConstraint::buildPattern($base, true, []));
    }

    public function test_build_pattern_returns_base_when_pattern_is_not_anchor_wrapped(): void
    {
        $base = '[A-Za-z0-9-_]+';

        $this->assertSame($base, SlugRouteConstraint::buildPattern($base, true, ['admin']));
    }
}
