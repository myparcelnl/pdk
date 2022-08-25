<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Collection;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Shipment\Model\Shipment;

/**
 * @property \MyParcelNL\Pdk\Shipment\Model\Shipment[] $items
 * @property \MyParcelNL\Pdk\Shipment\Model\Label      $label
 * @method Shipment first(callable $callback = null, $default = null)
 * @method Shipment last(callable $callback = null, $default = null)
 * @method Shipment pop()
 * @method Shipment shift()
 * @method Shipment[] all()
 */
class ShipmentCollection extends Collection
{
    /**
     * @var \MyParcelNL\Pdk\Shipment\Model\Label
     */
    public $label;

    /**
     * @var class-string
     */
    protected $cast = Shipment::class;

    /**
     * Set ID and reference ID of shipment from API response.
     *
     * @param  \MyParcelNL\Pdk\Base\Support\Collection $ids
     *
     * @return $this
     */
    public function addIds(Collection $ids): self
    {
        $this->each(function (Shipment $shipment, int $index) use ($ids) {
            $shipment->fill($ids->offsetGet($index) ?? []);
            return $shipment;
        });

        return $this;
    }
}
