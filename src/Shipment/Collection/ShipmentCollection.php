<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Collection;

use MyParcelNL\Pdk\Base\Contract\StorableArrayable;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Shipment\Model\Shipment;

/**
 * @property \MyParcelNL\Pdk\Shipment\Model\Shipment[] $items
 */
class ShipmentCollection extends Collection implements StorableArrayable
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

    /**
     * @return void
     */
    public function toStorableArray(): array
    {
        return $this
            ->where('deleted', null)
            ->map(function (Shipment $shipment) {
                return $shipment->toStorableArray();
            })
            ->toArray();
    }
}
