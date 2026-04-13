<?php

declare(strict_types=1);

namespace MB\MoonShine\Tests\Feature;

use MB\MoonShine\Support\PublicPageUrl;
use MB\MoonShine\Tests\TestCase;

final class PublicPageUrlTest extends TestCase
{
    public function test_returns_null_when_page_is_inactive(): void
    {
        $this->assertNull(PublicPageUrl::absolute([
            'slug' => 'about',
            'is_active' => false,
        ]));
    }

    public function test_returns_absolute_url_for_active_page_when_route_exists(): void
    {
        $url = PublicPageUrl::absolute([
            'slug' => 'about',
            'is_active' => true,
        ]);

        $this->assertNotNull($url);
        $this->assertStringContainsString('about', $url);
    }

    public function test_admin_public_url_ignores_active_flag(): void
    {
        $url = PublicPageUrl::adminPublicUrl([
            'slug' => 'draft',
            'is_active' => false,
        ]);

        $this->assertNotNull($url);
        $this->assertStringContainsString('draft', $url);
    }
}
