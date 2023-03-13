<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Contract;

use MyParcelNL\Pdk\Plugin\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\Plugin\Model\PdkOrder;

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
     * Update order data.
     */
    public function update(PdkOrder $order): PdkOrder;

    /**
     * Update order data in bulk.
     */
    public function updateMany(PdkOrderCollection $collection): PdkOrderCollection;
}
