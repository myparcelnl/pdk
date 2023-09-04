<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Repository\AbstractPdkOrderRepository;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Storage\Contract\StorageDriverInterface;

class MockPdkOrderRepository extends AbstractPdkOrderRepository
{
    /**
     * @param  PdkOrder|PdkOrder[]|PdkOrderCollection                  $orders
     * @param  \MyParcelNL\Pdk\Storage\Contract\StorageDriverInterface $storage
     *
     * @noinspection PhpOptionalBeforeRequiredParametersInspection
     */
    public function __construct($orders = [], StorageDriverInterface $storage)
    {
        parent::__construct($storage);

        $collection = $orders instanceof PdkOrderCollection
            ? $orders
            : new PdkOrderCollection(Arr::wrap($orders));

        $this->updateMany($collection);
    }

    /**
     * @param  int|string $input
     *
     * @return \MyParcelNL\Pdk\App\Order\Model\PdkOrder
     */
    public function get($input): PdkOrder
    {
        $orderData = is_array($input) ? $input : ['externalIdentifier' => $input];

        return $this->retrieve((string) $orderData['externalIdentifier'], function () use ($orderData) {
            return new PdkOrder($orderData);
        });
    }
}
