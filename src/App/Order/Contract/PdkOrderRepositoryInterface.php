<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Contract;

use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;

interface PdkOrderRepositoryInterface
{
    /**
     * Create a new order object from input data.
     */
    public function get($input): PdkOrder;

    /**
     * Create a collection of order objects from input data
     *
     * @param  string|string[] $orderIds - Single id, array of ids or string of semicolon-separated ids.
     */
    public function getMany($orderIds): PdkOrderCollection;

    /**
     * Get the order notes for a given order.
     *
     * @param  null|string $externalIdentifier
     *
     * @return array
     */
    public function getOrderNotes(?string $externalIdentifier): array;

    /**
     * Update order data.
     */
    public function update(PdkOrder $order): PdkOrder;

    /**
     * Update order data in bulk.
     */
    public function updateMany(PdkOrderCollection $collection): PdkOrderCollection;
}
