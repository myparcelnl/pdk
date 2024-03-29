<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Service;

use MyParcelNL\Pdk\Frontend\Contract\ViewServiceInterface;

abstract class AbstractViewService implements ViewServiceInterface
{
    /**
     * @return bool
     */
    public function hasModals(): bool
    {
        return $this->isOrderListPage() || $this->isOrderPage();
    }

    /**
     * @return bool
     */
    public function hasNotifications(): bool
    {
        return $this->isAnyPdkPage();
    }

    /**
     * @return bool
     */
    public function isAnyPdkPage(): bool
    {
        return $this->isChildProductPage()
            || $this->isOrderListPage()
            || $this->isOrderPage()
            || $this->isPluginSettingsPage()
            || $this->isProductPage();
    }
}
