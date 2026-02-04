<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Contract;

use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Contract\ModelRepositoryInterface;
use MyParcelNL\Pdk\Base\Contract\RepositoryInterface;

interface PdkOrderRepositoryInterface extends ModelRepositoryInterface, RepositoryInterface
{
    /**
     * Create a new order object from input data.
     *
     * @deprecated this will be removed in a future release. Switch to instantiating PdkOrder directly if you want a new order. Use find() to retrieve an existing order.
     */
    public function get($input): PdkOrder;

    /**
     * @inheritdoc
     * Returns a PdkOrder or null if not found.
     */
    public function find($id): ?PdkOrder;

    /**
     * Create a collection of order objects from input data
     *
     * @param  string|string[] $orderIds - Single id, array of ids or string of semicolon-separated ids.
     * @deprecated this will be removed in a future release. Switch to instantiating PdkOrderCollection directly. Or use `findAll()` to retrieve existing orders.
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
