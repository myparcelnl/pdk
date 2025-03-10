<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Collection;

use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use MyParcelNL\Pdk\Validation\Validator\CarrierSchema;
use MyParcelNL\Pdk\Base\Model\ContactDetails;

/**
 * @property \MyParcelNL\Pdk\App\Order\Model\PdkOrder[] $items
 */
class PdkOrderCollection extends Collection
{
    protected $cast = PdkOrder::class;

    /**
     * @param  \MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection $orders
     *
     * @return self
     */
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
     * @return \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection
     * @throws \Exception
     */
    public function generateShipments(): ShipmentCollection
    {
        $newShipments = new ShipmentCollection();

        $this->each(function (PdkOrder $order) use ($newShipments) {
            $schema = Pdk::get(CarrierSchema::class);

            $schema->setCarrier($order->deliveryOptions->carrier);

            $amountOfShipmentsToCreate = $schema->canHaveMultiCollo() ? 1 : $order->deliveryOptions->labelAmount;

            for ($i = 0; $i < $amountOfShipmentsToCreate; $i++) {
                $newShipments->push($order->createShipment());
            }
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
            ->reduce(function (ShipmentCollection $collection, ShipmentCollection $shipments) {
                $lastShipment = $shipments->last();
                $labelAmount  = $lastShipment->deliveryOptions->labelAmount;
                $offset       = $shipments->count() - $labelAmount;
                $allShipments = $shipments->slice($offset, $labelAmount);

                $allShipments->each(function (Shipment $shipment) use ($collection) {
                    // Ensure recipient data is present
                    if (!$shipment->recipient && $shipment->orderId) {
                        $order = $this->firstWhere('externalIdentifier', $shipment->orderId);
                        if ($order && $order->shippingAddress) {
                            // Create a new ContactDetails object from the shipping address
                            $shipment->recipient = new ContactDetails([
                                'address1' => $order->shippingAddress->address1,
                                'address2' => $order->shippingAddress->address2,
                                'cc' => $order->shippingAddress->cc,
                                'city' => $order->shippingAddress->city,
                                'postalCode' => $order->shippingAddress->postalCode,
                                'region' => $order->shippingAddress->region,
                                'state' => $order->shippingAddress->state,
                                'email' => $order->shippingAddress->email,
                                'phone' => $order->shippingAddress->phone,
                                'person' => $order->shippingAddress->person,
                                'company' => $order->shippingAddress->company
                            ]);
                        }
                    }
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
     * @param  \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection $shipments
     *
     * @return void
     */
    public function updateShipments(ShipmentCollection $shipments): void
    {
        foreach ($this as $order) {
            $orderShipments = $shipments->filter(function (Shipment $shipment) use ($order) {
                return $shipment->orderId === $order->externalIdentifier;
            });

            if ($orderShipments->isEmpty()) {
                continue;
            }

            // Update the recipient data from the shipping address
            $lastShipment = $orderShipments->last();
            if ($lastShipment && $order->shippingAddress) {
                $lastShipment->recipient = new ContactDetails([
                    'address1' => $order->shippingAddress->address1,
                    'address2' => $order->shippingAddress->address2,
                    'cc' => $order->shippingAddress->cc,
                    'city' => $order->shippingAddress->city,
                    'postalCode' => $order->shippingAddress->postalCode,
                    'region' => $order->shippingAddress->region,
                    'state' => $order->shippingAddress->state,
                    'email' => $order->shippingAddress->email,
                    'phone' => $order->shippingAddress->phone,
                    'person' => $order->shippingAddress->person,
                    'company' => $order->shippingAddress->company
                ]);
            }

            $order->shipments = $orderShipments;
        }
    }
}
