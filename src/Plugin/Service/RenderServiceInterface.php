<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Service;

use MyParcelNL\Pdk\Plugin\Model\PdkOrder;

interface RenderServiceInterface
{
    /**
     * @return string
     */
    public function renderInitScript(): string;

    /**
     * @return string
     */
    public function renderModals(): string;

    /**
     * @return string
     */
    public function renderNotifications(): string;

    /**
     * @param  \MyParcelNL\Pdk\Plugin\Model\PdkOrder $order
     *
     * @return string
     */
    public function renderOrderCard(PdkOrder $order): string;

    /**
     * @param  \MyParcelNL\Pdk\Plugin\Model\PdkOrder $order
     *
     * @return string
     */
    public function renderOrderListColumn(PdkOrder $order): string;
}


