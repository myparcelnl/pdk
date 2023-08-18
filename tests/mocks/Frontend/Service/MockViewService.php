<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Service;

use MyParcelNL\Pdk\Frontend\Contract\ViewServiceInterface;

final class MockViewService implements ViewServiceInterface
{
    public function hasModals(): bool
    {
        return true;
    }

    public function hasNotifications(): bool
    {
        return true;
    }

    public function isAnyPdkPage(): bool
    {
        return true;
    }

    public function isCheckoutPage(): bool
    {
        return true;
    }

    public function isChildProductPage(): bool
    {
        return true;
    }

    public function isOrderListPage(): bool
    {
        return true;
    }

    public function isOrderPage(): bool
    {
        return true;
    }

    public function isPluginSettingsPage(): bool
    {
        return true;
    }

    public function isProductPage(): bool
    {
        return true;
    }
}
