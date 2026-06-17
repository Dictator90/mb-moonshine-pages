<?php

declare(strict_types=1);

namespace MB\MoonShine\Tests\Feature;

use Illuminate\Support\Facades\Validator;
use MB\MoonShine\MoonShine\Resources\Page\Pages\PageFormPage;
use MB\MoonShine\Tests\TestCase;
use MoonShine\Core\TypeCasts\MixedDataWrapper;
use ReflectionClass;
use ReflectionMethod;

final class PageSlugValidationTest extends TestCase
{
    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        $page = (new ReflectionClass(PageFormPage::class))->newInstanceWithoutConstructor();

        $method = new ReflectionMethod(PageFormPage::class, 'rules');
        $method->setAccessible(true);

        /** @var array<string, mixed> $rules */
        $rules = $method->invoke($page, new MixedDataWrapper([], null));

        return $rules;
    }

    private function slugFails(string $slug): bool
    {
        $rules = $this->rules();

        return Validator::make(['slug' => $slug], ['slug' => $rules['slug']])->fails();
    }

    public function test_multi_segment_slug_passes_validation(): void
    {
        // Regression: the regex rule must not break on the "/" inside the
        // multi-segment slug pattern (delimiter collision -> preg_match error).
        $this->assertFalse($this->slugFails('catalog/feature/common'));
        $this->assertFalse($this->slugFails('politics'));
    }

    public function test_malformed_slugs_fail_validation(): void
    {
        $this->assertTrue($this->slugFails('/leading'));
        $this->assertTrue($this->slugFails('trailing/'));
        $this->assertTrue($this->slugFails('double//slash'));
        $this->assertTrue($this->slugFails('bad space'));
    }
}
