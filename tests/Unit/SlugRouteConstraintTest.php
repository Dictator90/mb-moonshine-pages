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

        $this->assertSame('^(?!(?:admin)$)[A-Za-z0-9-_]+$', $pattern);
    }

    public function test_build_pattern_supports_multiple_reserved_segments(): void
    {
        $pattern = SlugRouteConstraint::buildPattern('^[A-Za-z0-9-_]+$', true, ['admin', 'up']);

        $this->assertSame('^(?!(?:admin|up)$)[A-Za-z0-9-_]+$', $pattern);
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
