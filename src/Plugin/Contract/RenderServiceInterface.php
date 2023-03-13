<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Contract;

use MyParcelNL\Pdk\Plugin\Model\PdkCart;
use MyParcelNL\Pdk\Plugin\Model\PdkOrder;
use MyParcelNL\Pdk\Plugin\Model\PdkProduct;

interface RenderServiceInterface
{
    /**
     * Renders the delivery options component.
     */
    public function renderDeliveryOptions(PdkCart $cart): string;

    /**
     * Renders the init script needed for all other components.
     */
    public function renderInitScript(): string;

    /**
     * Renders the component containing all modals.
     */
    public function renderModals(): string;

    /**
     * Renders the main notifications component.
     */
    public function renderNotifications(): string;

    /**
     * Renders a box containing a single order's options.
     */
    public function renderOrderBox(PdkOrder $order): string;

    /**
     * Renders a small version of the order box for a single order.
     */
    public function renderOrderListItem(PdkOrder $order): string;

    /**
     * Renders the plugin settings.
     */
    public function renderPluginSettings(): string;

    /**
     * Renders a product's settings.
     */
    public function renderProductSettings(PdkProduct $product): string;
}


