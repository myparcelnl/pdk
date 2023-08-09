<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Contract;

interface ViewServiceInterface
{
    /**
     * Whether the current page should have PDK modals.
     *
     * @see \MyParcelNL\Pdk\Facade\Frontend::renderModals()
     */
    public function hasModals(): bool;

    /**
     * Whether the current page should have PDK notifications.
     *
     * @see \MyParcelNL\Pdk\Facade\Frontend::renderNotifications()
     */
    public function hasNotifications(): bool;

    /**
     * Whether the current page is a page that renders a PDK component. This is used to determine whether the PDK
     * stylesheets and scripts should be loaded.
     */
    public function isAnyPdkPage(): bool;

    /**
     * True if the current page is the frontend checkout page.
     *
     * @see \MyParcelNL\Pdk\Facade\Frontend::renderDeliveryOptions()
     */
    public function isCheckoutPage(): bool;

    /**
     * True if the current page is the child product page.
     *
     * @see \MyParcelNL\Pdk\Facade\Frontend::renderChildProductSettings()
     */
    public function isChildProductPage(): bool;

    /**
     * True if the current page is the order list page.
     *
     * @see \MyParcelNL\Pdk\Facade\Frontend::renderOrderListItem()
     */
    public function isOrderListPage(): bool;

    /**
     * True if the current page is the order page.
     *
     * @see \MyParcelNL\Pdk\Facade\Frontend::renderOrderBox()
     */
    public function isOrderPage(): bool;

    /**
     * True if the current page is the plugin settings page.
     *
     * @see \MyParcelNL\Pdk\Facade\Frontend::renderPluginSettings()
     */
    public function isPluginSettingsPage(): bool;

    /**
     * True if the current page is the product page.
     *
     * @see \MyParcelNL\Pdk\Facade\Frontend::renderProductSettings()
     */
    public function isProductPage(): bool;
}
