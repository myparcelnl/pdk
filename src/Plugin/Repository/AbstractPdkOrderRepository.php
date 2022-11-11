<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Repository;

use MyParcelNL\Pdk\Base\Repository\ApiRepository;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Plugin\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\Plugin\Model\PdkOrder;

abstract class AbstractPdkOrderRepository extends ApiRepository
{
    /**
     * Create a new order object from input data.
     *
     * @param  mixed $input
     *
     * @return \MyParcelNL\Pdk\Plugin\Model\PdkOrder
     */
    abstract public function get($input): PdkOrder;

    /**
     * Create a collection of order objects from input data.
     *
     * @param  string|string[] $orderIds - Single id, array of ids or string of semicolon-separated ids.
     *
     * @return \MyParcelNL\Pdk\Plugin\Collection\PdkOrderCollection
     * @noinspection PhpUnused
     */
    public function getMany($orderIds): PdkOrderCollection
    {
        return new PdkOrderCollection(array_map([$this, 'get'], Utils::toArray($orderIds)));
    }

    /**
     * Update order data.
     *
     * @param  \MyParcelNL\Pdk\Plugin\Model\PdkOrder $order
     *
     * @return \MyParcelNL\Pdk\Plugin\Model\PdkOrder
     */
    public function update(PdkOrder $order): PdkOrder
    {
        return $this->save($order->externalIdentifier, $order);
    }

    /**
     * Update order data in bulk.
     *
     * @param  \MyParcelNL\Pdk\Plugin\Collection\PdkOrderCollection $collection
     *
     * @return \MyParcelNL\Pdk\Plugin\Collection\PdkOrderCollection
     */
    public function updateMany(PdkOrderCollection $collection): PdkOrderCollection
    {
        return $collection->map(function (PdkOrder $order) {
            return $this->update($order);
        });
    }
}
