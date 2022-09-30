<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Service;

use MyParcelNL\Pdk\Plugin\Model\PdkOrder;

interface RenderServiceInterface
{
    /**
     * Render the init script component. This component is responsible for initializing the javascript code needed to
     * render the frontend.
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function renderInitScript(): string;

    /**
     * Render the modals component. This component is responsible for rendering the modals used in the frontend.
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function renderModals(): string;

    /**
     * Render the notifications component. This component is responsible for rendering the notifications reported by
     * other parts of the frontend.
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function renderNotifications(): string;

    /**
     * Render the order card component. This component is responsible for rendering MyParcel information in a single
     * order view.
     *
     * @param  \MyParcelNL\Pdk\Plugin\Model\PdkOrder $order
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function renderOrderCard(PdkOrder $order): string;

    /**
     * Render the order list column component. This component is responsible for rendering MyParcel information for
     * each order in the order list/grid.
     *
     * @param  \MyParcelNL\Pdk\Plugin\Model\PdkOrder $order
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function renderOrderListColumn(PdkOrder $order): string;
}


