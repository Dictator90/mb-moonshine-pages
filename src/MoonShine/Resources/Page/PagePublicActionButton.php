<?php

declare(strict_types=1);

namespace MB\MoonShine\MoonShine\Resources\Page;

use MB\MoonShine\Support\PublicPageUrl;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\ActionButtonContract;
use MoonShine\UI\Components\ActionButton;

final class PagePublicActionButton
{
    public static function make(): ActionButtonContract
    {
        return ActionButton::make(
            url: static function (mixed $original, ?DataWrapperContract $casted): string {
                $subject = $original ?? $casted?->getOriginal();

                return PublicPageUrl::adminPublicUrl($subject) ?? '#';
            }
        )
            ->icon('link')
            ->square()
            ->blank()
            ->canSee(static function (mixed $original, ?DataWrapperContract $casted): bool {
                $subject = $original ?? $casted?->getOriginal();

                return $subject->is_active && PublicPageUrl::adminPublicUrl($subject) !== null;
            })
            ->showInLine();
    }
}
