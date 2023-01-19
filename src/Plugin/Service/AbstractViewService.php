<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Service;

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
        return $this->isOrderListPage() || $this->isOrderPage() || $this->isProductPage() || $this->isPluginSettingsPage();
    }
}
