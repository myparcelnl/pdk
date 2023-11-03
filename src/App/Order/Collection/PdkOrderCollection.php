<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Collection;

use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Model\Shipment;

/**
 * @property \MyParcelNL\Pdk\App\Order\Model\PdkOrder[] $items
 */
class PdkOrderCollection extends Collection
{
    protected $cast = PdkOrder::class;

    /**
     * @return \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection
     * @throws \Exception
     */
    public function generateShipments(): ShipmentCollection
    {
        $newShipments = new ShipmentCollection();

        $this->each(function (PdkOrder $order) use ($newShipments) {
            $newShipment = $order->createShipment();
            $newShipments->push($newShipment);
            $order->shipments->push($newShipment);
        });

        return $newShipments;
    }

    /**
     * @return \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection
     */
    public function getAllShipments(): ShipmentCollection
    {
        /** @var ShipmentCollection $collection */
        $collection = $this->reduce(function (ShipmentCollection $acc, PdkOrder $order) {
            $order->shipments->each(function (Shipment $shipment) use ($order) {
                $shipment->orderId = $order->externalIdentifier;
            });

            $acc->push(...$order->shipments->filterNotDeleted());

            return $acc;
        }, new ShipmentCollection());

        return $collection->values();
    }

    /**
     * @return \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection
     */
    public function getLastShipments(): ShipmentCollection
    {
        return $this->getAllShipments()
            ->groupBy('orderId')
            ->reduce(static function (ShipmentCollection $collection, ShipmentCollection $shipments) {
                $lastShipment = $shipments->last();
                $labelAmount  = $lastShipment->deliveryOptions->labelAmount;
                $offset       = $shipments->count() - $labelAmount;
                $allShipments = $shipments->slice($offset, $labelAmount);

                $allShipments->each(static function (Shipment $shipment) use ($collection) {
                    $collection->push($shipment);
                });

                return $collection;
            }, new ShipmentCollection());
    }

    /**
     * @param  int|int[] $shipmentIds
     *
     * @return \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection
     */
    public function getShipmentsByIds($shipmentIds): ShipmentCollection
    {
        return $this->getAllShipments()
            ->whereIn('id', is_array($shipmentIds) ? $shipmentIds : func_get_args())
            ->values();
    }

    /**
     * @return self
     */
    public function markAsExported(): self
    {
        return $this->map(static function (PdkOrder $order) {
            $order->exported = true;

            return $order;
        });
    }

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection $shipments
     *
     * @return $this
     */
    public function updateShipments(ShipmentCollection $shipments): self
    {
        $useOrderId = null !== $shipments->firstWhere('orderId', '!=', null);

        $this->each(function (PdkOrder $order) use ($shipments, $useOrderId) {
            $order->shipments = $useOrderId
                ? $this->mergeShipmentsByOrder($shipments, $order)
                : $this->mergeShipmentsById($shipments, $order);
        });

        return $this;
    }

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection $shipments
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrder               $order
     *
     * @return \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection
     */
    private function mergeShipmentsById(ShipmentCollection $shipments, PdkOrder $order): ShipmentCollection
    {
        $idShipments    = $shipments->keyBy('id');
        $orderShipments = $order->shipments->keyBy('id');

        foreach ($orderShipments as $id => $orderShipment) {
            /** @var null|\MyParcelNL\Pdk\Shipment\Model\Shipment $matchingShipment */
            $matchingShipment = $idShipments->get($id);

            if (! $matchingShipment) {
                continue;
            }

            $matchingShipment->orderId = $order->externalIdentifier;
            $orderShipments->put($id, $matchingShipment);
        }

        return $orderShipments->values();
    }

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection $shipments
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrder               $order
     *
     * @return \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection
     */
    private function mergeShipmentsByOrder(ShipmentCollection $shipments, PdkOrder $order): ShipmentCollection
    {
        $byOrderId = $shipments->where('orderId', $order->externalIdentifier);

        /** @var ShipmentCollection $merged */
        $merged = $order->shipments->mergeByKey($byOrderId, 'id');

        return $merged;
    }
}

