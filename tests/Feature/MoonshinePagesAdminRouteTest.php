<?php

declare(strict_types=1);

namespace MB\MoonShine\Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use MB\MoonShine\Tests\TestCase;

final class MoonshinePagesAdminRouteTest extends TestCase
{
    public function test_admin_is_not_matched_by_page_show_route(): void
    {
        $matched = Route::getRoutes()->match(Request::create('/admin', 'GET'));

        $this->assertNotSame('page.show', $matched->getName());
    }
}
