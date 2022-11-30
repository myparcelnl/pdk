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
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function generateShipments(array $data = []): ShipmentCollection
    {
        $this->each(function (PdkOrder $order) use ($data) {
            $order->createShipments($data);
        });

        return $this->getLastShipments();
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
     * @param  array $data
     *
     * @return \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection
     */
    public function getLastShipments(): ShipmentCollection
    {
        return $this->getAllShipments()
            ->groupBy('orderId')
            ->reduce(static function (ShipmentCollection $acc, ShipmentCollection $shipments) {
                $lastShipment = $shipments->last();
                $labelAmount  = $lastShipment->deliveryOptions->labelAmount;
                $offset       = $shipments->count() - $labelAmount;
                $allShipments = $shipments->slice($offset, $labelAmount);

                $allShipments->each(static function (Shipment $shipment) use ($acc) {
                    $acc->push($shipment);
                });

                return $acc;
            }, new ShipmentCollection());
    }

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection $shipments
     *
     * @return $this
     */
    public function updateShipments(ShipmentCollection $shipments): self
    {
        $this->each(function (PdkOrder $order) use ($shipments) {
            $byOrderId = $shipments->where('orderId', $order->externalIdentifier);

            if ($byOrderId->isEmpty()) {
                $order->shipments = $order->shipments
                    ->mergeByKey($shipments, 'id')
                    ->map(function (Shipment $shipment) use ($order) {
                        $shipment->orderId = $order->externalIdentifier;
                        return $shipment;
                    });
            } else {
                // Using values() to reset the collection keys.
                $order->shipments        = $byOrderId->values();
                $order->shipments->label = $shipments->label;
                $order->label            = $shipments->label;
            }
        });

        return $this;
    }
}
