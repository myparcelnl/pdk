<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Collection;

use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Model\Shipment;

/**
 * @property \MyParcelNL\Pdk\App\Order\Model\PdkOrder[] $items
 */
class PdkOrderCollection extends Collection
{
    protected $cast = PdkOrder::class;

    public function addApiIdentifiers(OrderCollection $orders): self
    {
        $this->each(function (PdkOrder $order) use ($orders) {
            $order->apiIdentifier = $orders
                ->firstWhere('externalIdentifier', $order->externalIdentifier)
                ->uuid;
        });

        return $this;
    }

    /**
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
     */
    public function getShipmentsByIds($shipmentIds): ShipmentCollection
    {
        return $this->getAllShipments()
            ->whereIn('id', is_array($shipmentIds) ? $shipmentIds : func_get_args())
            ->values();
    }

    /**
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
     * @return null|\MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection
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
     * @return \MyParcelNL\Pdk\Base\Support\Collection|\MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection
     */
    private function mergeShipmentsByOrder(ShipmentCollection $shipments, PdkOrder $order): ShipmentCollection
    {
        $byOrderId = $shipments->where('orderId', $order->externalIdentifier);

        return $order->shipments->mergeByKey($byOrderId, 'id');
    }
}

