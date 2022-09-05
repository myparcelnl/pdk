<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Collection;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Fulfilment\Model\Order;

/**
 * @property \MyParcelNL\Pdk\Fulfilment\Model\Order[] $items
 */
class OrderCollection extends Collection
{
    /**
     * @var class-string
     */
    protected $cast = Order::class;

    /**
     * @param  \MyParcelNL\Pdk\Base\Support\Collection $ids
     *
     * @return $this
     */
    public function addIds(Collection $ids): self
    {
        $uuids = $ids->pluck('uuid');

        return $this->map(function (Order $order, int $index) use ($uuids) {
            $order->uuid = $uuids[$index];

            return $order;
        });
    }
}
