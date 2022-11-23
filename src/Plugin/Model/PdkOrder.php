<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Model;

use MyParcelNL\Pdk\Base\Model\ContactDetails;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Plugin\Collection\PdkOrderLineCollection;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Model\CustomsDeclaration;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\Label;
use MyParcelNL\Pdk\Shipment\Model\PhysicalProperties;
use MyParcelNL\Pdk\Shipment\Model\Shipment;

/**
 * @property null|string                                                   $externalIdentifier
 * @property null|\MyParcelNL\Pdk\Shipment\Model\CustomsDeclaration        $customsDeclaration
 * @property null|\MyParcelNL\Pdk\Shipment\Model\DeliveryOptions           $deliveryOptions
 * @property null|\MyParcelNL\Pdk\Plugin\Collection\PdkOrderLineCollection $lines
 * @property null|\MyParcelNL\Pdk\Base\Model\ContactDetails                $recipient
 * @property null|\MyParcelNL\Pdk\Base\Model\ContactDetails                $sender
 * @property null|int                                                      $shipmentPrice
 * @property null|int                                                      $shipmentPriceAfterVat
 * @property null|int                                                      $shipmentVat
 * @property null|\MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection   $shipments
 * @property null|\MyParcelNL\Pdk\Shipment\Model\Label                     $label
 */
class PdkOrder extends Model
{
    protected $attributes = [
        /** Plugin order id */
        'externalIdentifier'    => null,
        'customsDeclaration'    => CustomsDeclaration::class,
        'deliveryOptions'       => DeliveryOptions::class,
        'lines'                 => PdkOrderLineCollection::class,
        'physicalProperties'    => PhysicalProperties::class,
        'recipient'             => null,
        'sender'                => null,
        'shipmentPrice'         => null,
        'shipmentVat'           => null,
        'shipments'             => ShipmentCollection::class,
        'label'                 => null,
        /** Totals */
        'orderPrice'            => 0,
        'orderVat'              => 0,
        'orderPriceAfterVat'    => 0,
        'shipmentPriceAfterVat' => 0,
        'totalPrice'            => 0,
        'totalVat'              => 0,
        'totalPriceAfterVat'    => 0,
    ];

    protected $casts      = [
        'externalIdentifier'    => 'string',
        'customsDeclaration'    => CustomsDeclaration::class,
        'deliveryOptions'       => DeliveryOptions::class,
        'lines'                 => PdkOrderLineCollection::class,
        'physicalProperties'    => PhysicalProperties::class,
        'recipient'             => ContactDetails::class,
        'sender'                => ContactDetails::class,
        'shipmentPrice'         => 'int',
        'shipmentPriceAfterVat' => 'int',
        'shipmentVat'           => 'int',
        'shipments'             => ShipmentCollection::class,
        'label'                 => Label::class,

        'orderPrice'         => 'int',
        'orderVat'           => 'int',
        'orderPriceAfterVat' => 'int',
        'totalPrice'         => 'int',
        'totalVat'           => 'int',
        'totalPriceAfterVat' => 'int',
    ];

    /**
     * @param  null|array $data
     */
    public function __construct(?array $data = [])
    {
        parent::__construct($data);
        $this->updateShipments();
        $this->updateOrderTotals();
    }

    /**
     * @param  array $data
     *
     * @return \MyParcelNL\Pdk\Shipment\Model\Shipment
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function createShipment(array $data = []): Shipment
    {
        $this->shipments->push(
            array_replace_recursive(
                [
                    'deliveryOptions'    => $this->deliveryOptions,
                    'recipient'          => $this->recipient,
                    'sender'             => $this->sender,
                    'carrier'            => [
                        'name' => $this->deliveryOptions->carrier,
                    ],
                    ['orderId' => $this->externalIdentifier],
                    'physicalProperties' => [
                        'weight' => $this->customsDeclaration->weight,
                    ],
                ],
                $data
            )
        );

        return $this->getAttribute('shipments')
            ->last();
    }

    public function updateOrderTotals(): void
    {
        $price         = 0;
        $vat           = 0;
        $priceAfterVat = 0;

        foreach ($this->lines as $line) {
            $price         += $line->quantity * $line->getPrice();
            $vat           += $line->quantity * $line->getVat();
            $priceAfterVat += $line->quantity * $line->getPriceAfterVat();
        }

        $this->attributes['orderPrice']         = $price;
        $this->attributes['orderPriceAfterVat'] = $priceAfterVat;
        $this->attributes['orderVat']           = $vat;
        $this->attributes['totalPrice']         = $price + $this->shipmentPrice;
        $this->attributes['totalPriceAfterVat'] = $priceAfterVat + $this->shipmentPriceAfterVat;
        $this->attributes['totalVat']           = $vat + $this->shipmentVat;
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
