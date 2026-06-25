<?php

declare(strict_types=1);

namespace MB\MoonShine\Tests\Feature;

use MB\MoonShine\MoonShine\Resources\Page\Pages\PageFormPage;
use MB\MoonShine\Tests\TestCase;
use MoonShine\CKEditor\Fields\CKEditor;
use MoonShine\TinyMce\Fields\TinyMce;
use MoonShine\UI\Fields\Textarea;
use ReflectionClass;
use ReflectionMethod;

final class PageContentFieldConfigTest extends TestCase
{
    private function resolveContentField(): object
    {
        $page = (new ReflectionClass(PageFormPage::class))->newInstanceWithoutConstructor();

        $method = new ReflectionMethod(PageFormPage::class, 'resolveContentField');
        $method->setAccessible(true);

        return $method->invoke($page);
    }

    public function test_default_content_field_is_tinymce(): void
    {
        $this->assertInstanceOf(TinyMce::class, $this->resolveContentField());
    }

    public function test_content_field_can_be_swapped_via_config(): void
    {
        config(['moonshine-pages.fields.page_content' => CKEditor::class]);

        $this->assertInstanceOf(CKEditor::class, $this->resolveContentField());
    }

    public function test_invalid_content_field_falls_back_to_textarea(): void
    {
        config(['moonshine-pages.fields.page_content' => 'Not\\A\\Real\\Class']);

        $field = $this->resolveContentField();

        $this->assertSame(Textarea::class, $field::class);
    }
}
