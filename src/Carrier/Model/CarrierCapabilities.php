<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Model;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Shipment\Collection\DeliveryTypeCollection;
use MyParcelNL\Pdk\Shipment\Model\PackageType;

/**
 * @property null|\MyParcelNL\Pdk\Shipment\Model\PackageType                 $packageType
 * @property null|\MyParcelNL\Pdk\Shipment\Collection\DeliveryTypeCollection $deliveryTypes
 * @property null|\MyParcelNL\Pdk\Shipment\Model\ShipmentOptions             $shipmentOptions
 */
class CarrierCapabilities extends Model
{
    protected $attributes = [
        'packageType'     => PackageType::class,
        'deliveryTypes'   => DeliveryTypeCollection::class,
        'shipmentOptions' => ShipmentOptionsCapabilities::class,
    ];

    protected $casts      = [
        'packageType'     => PackageType::class,
        'deliveryTypes'   => DeliveryTypeCollection::class,
        'shipmentOptions' => ShipmentOptionsCapabilities::class,
    ];
}
