<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Model\Options;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Carrier\Collection\DeliveryTypeCollection;
use MyParcelNL\Pdk\Shipment\Model\Options\ShipmentOptions;

/**
 * @property null|\MyParcelNL\Pdk\Carrier\Model\Options\PackageType         $packageType
 * @property null|\MyParcelNL\Pdk\Carrier\Collection\DeliveryTypeCollection $deliveryTypes
 * @property null|\MyParcelNL\Pdk\Shipment\Model\Options\ShipmentOptions    $shipmentOptions
 */
class CarrierOptions extends Model
{
    protected $attributes = [
        'packageType'     => PackageType::class,
        'deliveryTypes'   => DeliveryTypeCollection::class,
        'shipmentOptions' => ShipmentOptions::class,
    ];

    protected $casts      = [
        'packageType'     => PackageType::class,
        'deliveryTypes'   => DeliveryTypeCollection::class,
        'shipmentOptions' => ShipmentOptions::class,
    ];
}
