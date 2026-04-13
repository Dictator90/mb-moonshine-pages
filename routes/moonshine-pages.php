<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use MB\MoonShine\Http\Controllers\PageShowController;
use MB\MoonShine\Support\SlugRouteConstraint;

$routePrefix = trim((string) config('moonshine-pages.route.route_prefix', ''), '/');
$pagePath = $routePrefix === '' ? '/{slug}' : sprintf('/%s/{slug}', $routePrefix);
$controller = (string) config('moonshine-pages.controllers.page_show', PageShowController::class);

Route::get($pagePath, $controller)
    ->where('slug', SlugRouteConstraint::pattern())
    ->name((string) config('moonshine-pages.route.name', 'page.show'));
