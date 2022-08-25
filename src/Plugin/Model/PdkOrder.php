<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Model;

use MyParcelNL\Pdk\Base\Model\ContactDetails;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Model\CustomsDeclaration;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\Label;
use MyParcelNL\Pdk\Shipment\Model\Shipment;

/**
 * @property null|string                                                 $externalIdentifier
 * @property null|\MyParcelNL\Pdk\Shipment\Model\CustomsDeclaration      $customsDeclaration
 * @property null|\MyParcelNL\Pdk\Shipment\Model\DeliveryOptions         $deliveryOptions
 * @property null|\MyParcelNL\Pdk\Base\Model\ContactDetails              $recipient
 * @property null|\MyParcelNL\Pdk\Base\Model\ContactDetails              $sender
 * @property null|\MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection $shipments
 * @property null|\MyParcelNL\Pdk\Shipment\Model\Label                   $label
 */
class PdkOrder extends Model
{
    protected $attributes = [
        /** Plugin order id */
        'externalIdentifier' => null,
        'customsDeclaration' => CustomsDeclaration::class,
        'deliveryOptions'    => DeliveryOptions::class,
        'recipient'          => null,
        'sender'             => null,
        'shipments'          => ShipmentCollection::class,
        'label'              => null,
    ];

    protected $casts      = [
        'externalIdentifier' => 'string',
        'customsDeclaration' => CustomsDeclaration::class,
        'deliveryOptions'    => DeliveryOptions::class,
        'recipient'          => ContactDetails::class,
        'sender'             => ContactDetails::class,
        'shipments'          => ShipmentCollection::class,
        'label'              => Label::class,
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
     * @param  array $data
     *
     * @return \MyParcelNL\Pdk\Shipment\Model\Shipment
     */
    public function createShipment(array $data = []): Shipment
    {
        $this->shipments->push(
            array_replace_recursive(
                [
                    'deliveryOptions' => $this->deliveryOptions,
                    'recipient'       => $this->recipient,
                    'sender'          => $this->sender,
                ],
                $data,
                ['orderId' => $this->externalIdentifier]
            )
        );

        return $this->shipments->last();
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
            $shipment->orderId         = $this->externalIdentifier;
            $shipment->deliveryOptions = $this->deliveryOptions;
            $shipment->recipient       = $this->recipient;
            $shipment->sender          = $this->sender;
        });
    }
}
