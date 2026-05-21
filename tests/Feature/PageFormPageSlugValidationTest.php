<?php

declare(strict_types=1);

namespace MB\MoonShine\Tests\Feature;

use MB\MoonShine\Tests\TestCase;

final class PageFormPageSlugValidationTest extends TestCase
{
    public function test_slug_unique_rule_does_not_append_empty_id_for_create(): void
    {
        $contents = file_get_contents(__DIR__.'/../../src/MoonShine/Resources/Page/Pages/PageFormPage.php');

        $this->assertIsString($contents);
        $this->assertStringContainsString('Rule::unique(MoonShinePagesTables::pages(), \'slug\')', $contents);
        $this->assertStringNotContainsString("',slug,'.\$id", $contents);
        $this->assertStringContainsString("if (\$id !== null && \$id !== '')", $contents);
    }
}
