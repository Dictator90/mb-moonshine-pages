<?php

declare(strict_types=1);

namespace MB\MoonShine\Tests\Feature;

use MB\MoonShine\MoonShine\Resources\Page\PageResource;
use MB\MoonShine\Tests\TestCase;
use ReflectionClass;
use ReflectionMethod;

final class PageSeoFieldsConfigTest extends TestCase
{
    /**
     * @return list<string>
     */
    private function search(): array
    {
        $resource = (new ReflectionClass(PageResource::class))->newInstanceWithoutConstructor();

        $method = new ReflectionMethod(PageResource::class, 'search');
        $method->setAccessible(true);

        /** @var list<string> $columns */
        $columns = $method->invoke($resource);

        return $columns;
    }

    public function test_seo_fields_enabled_by_default(): void
    {
        $this->assertTrue(PageResource::seoFieldsEnabled());
        $this->assertContains('seo_title', $this->search());
    }

    public function test_seo_fields_can_be_disabled_via_config(): void
    {
        config(['moonshine-pages.fields.page_seo' => false]);

        $this->assertFalse(PageResource::seoFieldsEnabled());
    }

    public function test_search_excludes_seo_title_when_disabled(): void
    {
        config(['moonshine-pages.fields.page_seo' => false]);

        $this->assertNotContains('seo_title', $this->search());
    }
}
