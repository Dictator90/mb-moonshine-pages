<?php

declare(strict_types=1);

namespace MB\MoonShine\Tests\Feature;

use MB\MoonShine\Models\Page;
use MB\MoonShine\Support\PublicPageUrl;
use MB\MoonShine\Tests\TestCase;

final class MultiSegmentSlugTest extends TestCase
{
    public function test_page_with_multi_segment_slug_is_reachable(): void
    {
        Page::query()->create([
            'title' => 'Common feature',
            'slug' => 'catalog/feature/common',
            'content' => '<p>Body</p>',
            'is_active' => true,
        ]);

        $this->get('/catalog/feature/common')->assertOk();
    }

    public function test_missing_multi_segment_slug_returns_404(): void
    {
        $this->get('/catalog/feature/missing')->assertNotFound();
    }

    public function test_public_url_keeps_slashes_unencoded(): void
    {
        $url = PublicPageUrl::absolute([
            'slug' => 'catalog/feature/common',
            'is_active' => true,
        ]);

        $this->assertNotNull($url);
        $this->assertStringContainsString('catalog/feature/common', $url);
        $this->assertStringNotContainsString('%2F', $url);
    }
}
