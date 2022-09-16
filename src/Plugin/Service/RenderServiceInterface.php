<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Service;

use MyParcelNL\Pdk\Plugin\Model\PdkOrder;

interface RenderServiceInterface
{
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
    public function renderOrderCard(PdkOrder $order): string;

    /**
     * @param  \MyParcelNL\Pdk\Plugin\Model\PdkOrder $order
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function renderOrderListColumn(PdkOrder $order): string;
}


