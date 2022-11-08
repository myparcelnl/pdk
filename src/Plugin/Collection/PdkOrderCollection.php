<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Collection;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Plugin\Model\PdkOrder;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Model\Shipment;

/**
 * @property \MyParcelNL\Pdk\Plugin\Model\PdkOrder[] $items
 */
class PdkOrderCollection extends Collection
{
    protected $cast = PdkOrder::class;

    /**
     * @param  array $data
     *
     * @return \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection
     */
    public function generateShipments(array $data = []): ShipmentCollection
    {
        $this->each(function (PdkOrder $order) use ($data) {
            $order->createShipment($data);
        });

        return $this->getAllShipments();
    }

    /**
     * @return \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection
     */
    public function getAllShipments(): ShipmentCollection
    {
        return $this->reduce(function (ShipmentCollection $acc, PdkOrder $order) {
            $order->shipments->each(function (Shipment $shipment) use ($order) {
                $shipment->orderId = $order->externalIdentifier;
            });

            $acc->push(...$order->shipments);
            return $acc;
        }, new ShipmentCollection());
    }

    /**
     * @return \MyParcelNL\Pdk\Shipment\Model\Shipment[]
     */
    public function getLastShipments(): array
    {
        $groupOfShipments = $this->getAllShipments()
            ->groupBy('orderId')
            ->all();

        $result = [];

        foreach ($groupOfShipments as $orderId => $shipments) {
            $result[$orderId] = $shipments->last();
        }

        return $result;
    }

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection $shipments
     *
     * @return $this
     */
    public function updateShipments(ShipmentCollection $shipments): self
    {
        $this->each(function (PdkOrder $order) use ($shipments) {
            $order->shipments        = $shipments->where('orderId', $order->externalIdentifier);
            $order->shipments->label = $shipments->label;
            $order->label            = $shipments->label;
        });

        return $this;
    }
}
