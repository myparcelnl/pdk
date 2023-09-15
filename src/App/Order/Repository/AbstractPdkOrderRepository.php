<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Repository;

use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Repository\Repository;
use MyParcelNL\Pdk\Base\Support\Utils;

abstract class AbstractPdkOrderRepository extends Repository implements PdkOrderRepositoryInterface
{
    /**
     * @param  mixed $input
     */
    abstract public function get($input): PdkOrder;

    /**
     * @param  string|string[] $orderIds
     */
    public function getMany($orderIds): PdkOrderCollection
    {
        return new PdkOrderCollection(array_map($this->get(...), Utils::toArray($orderIds)));
    }

    public function update(PdkOrder $order): PdkOrder
    {
        return $this->save($order->externalIdentifier, $order);
    }

    public function updateMany(PdkOrderCollection $collection): PdkOrderCollection
    {
        return $collection->map(fn($order) => $this->update($order));
    }
}
