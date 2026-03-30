<?php

declare(strict_types=1);

namespace MB\MoonShine\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\View\View;
use MB\MoonShine\Models\Page;

class PageShowController
{
    public function __invoke(Request $request, string $slug): View
    {
        /** @var class-string<Model> $pageModel */
        $pageModel = (string) config('moonshine-pages.models.page', Page::class);

        $page = $pageModel::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $view = view()->exists('pages.show')
            ? 'pages.show'
            : 'moonshine-pages::pages.show';

        return view($view, [
            'page' => $page,
        ]);
    }
}
