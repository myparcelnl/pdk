<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Collection;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection;
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
     * @return \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection
     * @throws \Exception
     */
    public function generateReturnShipments(): ShipmentCollection
    {
        $this->each(function (PdkOrder $order) {
            $order->createShipment([
                'parent' => $order->id,
            ]);
        });
    }

    /**
     * @param  array $data
     *
     * @return \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection
     * @throws \Exception
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
     * @return \MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection
     * @throws \Exception
     */
    public function getOrderCollection(): OrderCollection
    {
        $orderCollection = new OrderCollection();
        $this->generateShipments();
        $this->each(function (PdkOrder $pdkOrder) use ($orderCollection) {
            $orderCollection->push($pdkOrder->convertToFulfilmentOrder());
        });

        return $orderCollection;
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
