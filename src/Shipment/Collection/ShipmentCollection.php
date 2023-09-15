<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Collection;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Shipment\Model\Shipment;

/**
 * @property \MyParcelNL\Pdk\Shipment\Model\Shipment[] $items
 */
class ShipmentCollection extends Collection
{
    /**
     * @var class-string
     */
    protected $cast = Shipment::class;

    /**
     * Set ID and reference ID of shipment from API response.
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

    public function filterNotDeleted(): self
    {
        return $this->where('deleted', null);
    }

    public function toStorableArray(): array
    {
        return $this
            ->filterNotDeleted()
            ->map(fn(Shipment $shipment) => $shipment->toStorableArray())
            ->toArrayWithoutNull();
    }
}
