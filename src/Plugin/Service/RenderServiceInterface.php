<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Service;

use MyParcelNL\Pdk\Plugin\Model\PdkCart;
use MyParcelNL\Pdk\Plugin\Model\PdkOrder;
use MyParcelNL\Pdk\Plugin\Model\PdkProduct;

interface RenderServiceInterface
{
    /**
     * This can be overridden if needed.
     *
     * @return string
     */
    public function getInitHtml(): string;

    /**
     * @param  \MyParcelNL\Pdk\Plugin\Model\PdkCart $cart
     *
     * @return string
     */
    public function renderDeliveryOptions(PdkCart $cart): string;

    /**
     * @return string
     * @noinspection PhpUnused
     */
    public function renderInitScript(): string;

    /**
     * @return string
     * @noinspection PhpUnused
     */
    public function renderModals(): string;

    /**
     * @return string
     * @noinspection PhpUnused
     */
    public function renderNotifications(): string;

    /**
     * @param  \MyParcelNL\Pdk\Plugin\Model\PdkOrder $order
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function renderOrderBox(PdkOrder $order): string;

    /**
     * @param  \MyParcelNL\Pdk\Plugin\Model\PdkOrder $order
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function renderOrderListColumn(PdkOrder $order): string;

    /**
     * @return string
     */
    public function renderPluginSettings(): string;

    /**
     * @param  \MyParcelNL\Pdk\Plugin\Model\PdkProduct $product
     *
     * @return string
     */
    public function renderProductSettings(PdkProduct $product): string;
}


