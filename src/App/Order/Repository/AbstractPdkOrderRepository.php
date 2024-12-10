<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Repository;

use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Repository\Repository;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Facade\Logger;

abstract class AbstractPdkOrderRepository extends Repository implements PdkOrderRepositoryInterface
{
    /**
     * @param  mixed $input
     *
     * @return \MyParcelNL\Pdk\App\Order\Model\PdkOrder
     */
    abstract public function get($input): PdkOrder;

    // TODO: v3.0.0 make method abstract to force implementation
    public function getByApiIdentifier(string $uuid): ?PdkOrder
    {
        Logger::notice(
            'Implement getByApiIdentifier, in PDK v3 it will be required.',
            [
                'class' => self::class,
            ]
        );

        return $this->get(['order_id' => $uuid]);
    }

    /**
     * @param  string|string[] $orderIds
     *
     * @return \MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection
     */
    public function getMany($orderIds): PdkOrderCollection
    {
        return new PdkOrderCollection(array_map([$this, 'get'], Utils::toArray($orderIds)));
    }

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrder $order
     *
     * @return \MyParcelNL\Pdk\App\Order\Model\PdkOrder
     */
    public function update(PdkOrder $order): PdkOrder
    {
        return $this->save($order->externalIdentifier, $order);
    }

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection $collection
     *
     * @return \MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection
     */
    public function updateMany(PdkOrderCollection $collection): PdkOrderCollection
    {
        return $collection->map(function ($order) {
            return $this->update($order);
        });
    }
}
