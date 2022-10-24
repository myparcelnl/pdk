<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Collection;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection;
use MyParcelNL\Pdk\Fulfilment\Model\Order;
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
     */
    public function getOrderCollection(): OrderCollection
    {
        $fulfilmentCollection = new OrderCollection();

        /** @var PdkOrder $pdkOrder */
        $this->each(function ($pdkOrder) use ($fulfilmentCollection) {
            $fulfilmentOrder = new Order([
                // TODO: AccountId from AccountRepository -> getAccount()
                'accountId'                   => 12512,
                'createdAt'                   => $pdkOrder->orderDate,
                'externalIdentifier'          => $pdkOrder->externalIdentifier,
                'language'                    => $pdkOrder->language,
                'fulfilmentPartnerIdentifier' => null,
                'invoiceAddress'              => $pdkOrder->recipient,
                'orderDate'                   => $pdkOrder->orderDate,
                'orderLines'                  => $pdkOrder->lines,
                'price'                       => $pdkOrder->orderPrice,
                'priceAfterVat'               => $pdkOrder->orderPriceAfterVat,
                'shipments'                   => $pdkOrder->shipments,
                // TODO: ShopId from ShopRepoistory -> getShop()
                'shopId'                      => 251,
                'status'                      => 2,
                'type'                        => null,
                'updatedAt'                   => null,
                'uuid'                        => null,
                'vat'                         => $pdkOrder->totalVat,
            ]);

            $fulfilmentCollection->push($fulfilmentOrder);
        });

        return $fulfilmentCollection;
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
