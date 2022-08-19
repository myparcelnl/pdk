<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Collection;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Fulfilment\Model\Order;

/**
 * @property \MyParcelNL\Pdk\Fulfilment\Model\Order[] $items
 * @method Order first(callable $callback = null, $default = null)
 * @method Order last(callable $callback = null, $default = null)
 * @method Order pop()
 * @method Order shift()
 * @method Order[] all()
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
        return (new static($this->items))->map(function (Order $order) use ($ids) {
            $order->uuid = $ids;

            return $order;
        });
    }
}
