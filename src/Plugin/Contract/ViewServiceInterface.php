<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Contract;

interface ViewServiceInterface
{
    /**
     * @return bool
     */
    public function hasModals(): bool;

    /**
     * @return bool
     */
    public function hasNotifications(): bool;

    /**
     * @return bool
     */
    public function isAnyPdkPage(): bool;

    /**
     * @return bool
     */
    public function isCheckoutPage(): bool;

    /**
     * @return bool
     */
    public function isOrderListPage(): bool;

    /**
     * @return bool
     */
    public function isOrderPage(): bool;

    /**
     * @return bool
     */
    public function isPluginSettingsPage(): bool;

    /**
     * @return bool
     */
    public function isProductPage(): bool;
}
