<?php

declare(strict_types=1);

namespace MB\MoonShine\Tests\Feature;

use MB\MoonShine\Tests\TestCase;

final class MenuLinkFieldTest extends TestCase
{
    private function formPageSource(): string
    {
        $contents = file_get_contents(__DIR__.'/../../src/MoonShine/Resources/Menu/Pages/MenuFormPage.php');

        $this->assertIsString($contents);

        return $contents;
    }

    /**
     * `fromRaw()` is never called by MoonShine 4, so the virtual "link" field
     * opened empty and saved that emptiness into `source_value`.
     */
    public function test_link_field_is_filled_through_change_fill(): void
    {
        $contents = $this->formPageSource();

        $this->assertStringNotContainsString('->fromRaw(function (mixed $raw): ?string {', $contents);
        $this->assertStringContainsString('->changeFill(static function (mixed $item): ?string {', $contents);
    }

    /**
     * Both the link field and the route select write `source_value`, and both are
     * applied on every save — each must write only for its own `source_type`.
     */
    public function test_source_value_is_written_by_a_single_guarded_helper(): void
    {
        $contents = $this->formPageSource();

        $this->assertStringContainsString('private function applySourceValue(mixed $menu, mixed $value): mixed', $contents);
        $this->assertStringNotContainsString("data_get(request()->all(), 'source_type')", $contents);
        $this->assertSame(2, substr_count($contents, '$this->applySourceValue($menu, $value)'));
    }
}
