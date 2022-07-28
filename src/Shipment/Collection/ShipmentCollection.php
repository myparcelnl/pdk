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
     * @param  \MyParcelNL\Pdk\Base\Support\Collection $ids
     *
     * @return $this
     */
    public function addIds(Collection $ids): self
    {
        return (new static($this->items))->map(function (Shipment $shipment) use ($ids) {
            $match        = $ids->firstWhere('referenceIdentifier', $shipment->referenceIdentifier);
            $shipment->id = $match['id'] ?? null;

            return $shipment;
        });
    }
}
