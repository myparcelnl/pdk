<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Model;

use MyParcelNL\Pdk\Base\Model\ContactDetails;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\Shipment;

/**
 * @property null|string                                                 $externalIdentifier
 * @property null|\MyParcelNL\Pdk\Shipment\Model\DeliveryOptions         $deliveryOptions
 * @property null|\MyParcelNL\Pdk\Base\Model\ContactDetails              $recipient
 * @property null|\MyParcelNL\Pdk\Base\Model\ContactDetails              $sender
 * @property null|\MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection $shipments
 */
class PdkOrder extends Model
{
    protected $attributes = [
        /** Plugin order id */
        'externalIdentifier' => null,
        'deliveryOptions'    => DeliveryOptions::class,
        'recipient'          => null,
        'sender'             => null,
        'shipments'          => ShipmentCollection::class,
    ];

    protected $casts      = [
        'externalIdentifier' => 'string',
        'deliveryOptions'    => DeliveryOptions::class,
        'recipient'          => ContactDetails::class,
        'sender'             => ContactDetails::class,
        'shipments'          => ShipmentCollection::class,
    ];

    /**
     * @param  null|array $data
     */
    public function __construct(?array $data = [])
    {
        parent::__construct($data);
        $this->updateShipments();
    }

    /**
     * @param $shipments
     *
     * @return \MyParcelNL\Pdk\Plugin\Model\PdkOrder
     * @noinspection PhpUnused
     */
    protected function setShipmentsAttribute($shipments): self
    {
        $this->attributes['shipments'] = $shipments;
        $this->updateShipments();
        return $this;
    }

    /**
     * @return void
     */
    private function updateShipments(): void
    {
        $this->shipments->each(function (Shipment $shipment) {
            $shipment->deliveryOptions = $this->deliveryOptions;
            $shipment->recipient       = $this->recipient;
            $shipment->sender          = $this->sender;
        });
    }
}
