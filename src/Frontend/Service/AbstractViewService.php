<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Service;

use MyParcelNL\Pdk\Frontend\Contract\ViewServiceInterface;

abstract class AbstractViewService implements ViewServiceInterface
{
    public function hasModals(): bool
    {
        return $this->isOrderListPage() || $this->isOrderPage();
    }

    public function hasNotifications(): bool
    {
        return $this->isAnyPdkPage();
    }

    public function isAnyPdkPage(): bool
    {
        return $this->isChildProductPage()
            || $this->isOrderListPage()
            || $this->isOrderPage()
            || $this->isPluginSettingsPage()
            || $this->isProductPage();
    }
}
