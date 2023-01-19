<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Plugin\Service\ViewServiceInterface;

class MockViewService implements ViewServiceInterface
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

    public function isOrderListPage(): bool
    {
        return true;
    }

    public function isOrderPage(): bool
    {
        return true;
    }
}
