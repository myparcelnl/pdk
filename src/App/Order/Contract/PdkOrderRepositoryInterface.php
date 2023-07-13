<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Contract;

use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderNoteCollection;

interface PdkOrderRepositoryInterface
{
    /**
     * Create a new order object from input data.
     *
     * @param $input
     *
     * @return \MyParcelNL\Pdk\App\Order\Model\PdkOrder
     */
    public function get($input): PdkOrder;

    /**
     * Create a collection of order objects from input data
     *
     * @param $orderIds
     *
     * @return \MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection
     */
    public function getMany($orderIds): PdkOrderCollection;

    /**
     * Get the order notes for a given order.
     *
     * @param  null|string $externalIdentifier
     *
     * @return \MyParcelNL\Pdk\Fulfilment\Collection\OrderNoteCollection
     */
    public function getOrderNotes(?string $externalIdentifier): OrderNoteCollection;

    /**
     * Update order data.
     *
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrder $order
     *
     * @return \MyParcelNL\Pdk\App\Order\Model\PdkOrder
     */
    public function update(PdkOrder $order): PdkOrder;

    /**
     * Update order data in bulk.
     *
     * @param  \MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection $collection
     *
     * @return \MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection
     */
    public function updateMany(PdkOrderCollection $collection): PdkOrderCollection;
}
