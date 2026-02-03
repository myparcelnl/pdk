<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Repository;

use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Contract\ModelInterface;
use MyParcelNL\Pdk\Base\Exception\ModelNotFoundException;
use MyParcelNL\Pdk\Base\Repository\Repository;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Facade\Logger;

abstract class AbstractPdkOrderRepository extends Repository implements PdkOrderRepositoryInterface
{
    /**
     * @inheritdoc
     * @param  mixed $input
     *
     * @return \MyParcelNL\Pdk\App\Order\Model\PdkOrder
     */
    abstract public function get($input): PdkOrder;

    /**
     * @inheritdoc
     *
     * Included for backward compatibility, will be made abstract in next version.
     */
    public function find($id): ?PdkOrder
    {
        Logger::notice(
            'Please implement find() in ' . self::class . ' to retrieve orders by their identifier.',
            [
                'class' => self::class,
            ]
        );
        return $this->get($id);
    }

    /**
     * @inheritdoc
     *
     * Included for backward compatibility, will be made abstract in next version.
     */
    public function findAll(array $ids): PdkOrderCollection
    {
        Logger::notice(
            'Please implement findAll() in ' . self::class . ' to retrieve orders by their identifier.',
            [
                'class' => self::class,
            ]
        );
        return $this->getMany($ids);
    }

    /**
     * @inheritdoc
     *
     * Always throws a not found exception until included by concrete implementation.
     * Included for backward compatibility, will be made abstract in next version.
     */
    public function findOrFail($id): PdkOrder
    {
        throw new ModelNotFoundException(PdkOrder::class, [$id]);
    }

    /**
     * @inheritdoc
     *
     * Always returns an empty array until included by concrete implementation.
     * Included for backward compatibility, will be made abstract in next version.
     */
    public function all(): PdkOrderCollection
    {
        return new PdkOrderCollection([]);
    }

    /**
     * Get an order by its API identifier. This is used *only* for Order v1 compatibility.
     * Order v2 will always communicate using the externalIdentifier (the ID of the Order in the webshop itself).
     * This function will be deprecated along with Order v1 support in a future release.
     *
     * TODO: v3.0.0 make method abstract to force implementation
     *
     * @param  string $uuid
     *
     * @return null|\MyParcelNL\Pdk\App\Order\Model\PdkOrder
     */
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
