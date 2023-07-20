<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Repository;

use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Repository\Repository;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Storage\Contract\CacheStorageInterface;

/**
 * @deprecated Use \MyParcelNL\Pdk\App\Order\Repository\WcPdkOrderRepository instead. Will be removed in v3.0.0.
 */
abstract class AbstractPdkOrderRepository extends Repository implements PdkOrderRepositoryInterface
{
    /**
     * @param  \MyParcelNL\Pdk\Storage\Contract\CacheStorageInterface $cache
     */
    public function __construct(CacheStorageInterface $cache)
    {
        parent::__construct($cache);
        Logger::reportDeprecatedClass(__CLASS__, PdkOrderRepository::class);
    }

    /**
     * @param  mixed $input
     *
     * @return \MyParcelNL\Pdk\App\Order\Model\PdkOrder
     */
    abstract public function get($input): PdkOrder;

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
