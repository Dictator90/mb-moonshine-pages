<?php

declare(strict_types=1);

namespace MB\MoonShine\MoonShine\Resources\Page\Pages;

use MB\MoonShine\MoonShine\Resources\Page\PagePublicActionButton;
use MB\MoonShine\MoonShine\Resources\Page\PageResource;
use MoonShine\Contracts\UI\ActionButtonContract;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\Support\ListOf;

/**
 * @extends DetailPage<PageResource>
 */
final class PageDetailPage extends DetailPage
{
    /**
     * @return ListOf<ActionButtonContract>
     */
    protected function buttons(): ListOf
    {
        return new ListOf(ActionButtonContract::class, [
            PagePublicActionButton::make(),
            $this->modifyEditButton(
                $this->getResource()->getEditButton(
                    isAsync: $this->isAsync(),
                )
            ),
            $this->modifyDeleteButton(
                $this->getResource()->getDeleteButton(
                    redirectAfterDelete: $this->getResource()->getRedirectAfterDelete(),
                    isAsync: false,
                )
            ),
        ]);
    }
}
