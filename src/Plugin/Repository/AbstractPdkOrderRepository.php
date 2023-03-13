<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Repository;

use MyParcelNL\Pdk\Base\Repository\Repository;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Plugin\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\Plugin\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\Plugin\Model\PdkOrder;

abstract class AbstractPdkOrderRepository extends Repository implements PdkOrderRepositoryInterface
{
    /**
     * @param  mixed $input
     *
     * @return \MyParcelNL\Pdk\Plugin\Model\PdkOrder
     */
    abstract public function get($input): PdkOrder;

    /**
     * @param  string|string[] $orderIds
     *
     * @return \MyParcelNL\Pdk\Plugin\Collection\PdkOrderCollection
     */
    public function getMany($orderIds): PdkOrderCollection
    {
        return new PdkOrderCollection(array_map([$this, 'get'], Utils::toArray($orderIds)));
    }

    /**
     * @param  \MyParcelNL\Pdk\Plugin\Model\PdkOrder $order
     *
     * @return \MyParcelNL\Pdk\Plugin\Model\PdkOrder
     */
    public function update(PdkOrder $order): PdkOrder
    {
        return $this->save($order->externalIdentifier, $order);
    }

    /**
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
