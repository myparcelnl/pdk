<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Model\Options;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Carrier\Collection\DeliveryTypeCollection;
use MyParcelNL\Pdk\Shipment\Model\Options\ShipmentOptions;

class PackageType extends Model
{
    protected $attributes = [
        'packageTypeId'   => null,
        'packageTypeName' => null,
        'deliveryTypes'   => DeliveryTypeCollection::class,
        'shipmentOptions' => ShipmentOptions::class,
    ];

    protected $casts      = [
        'packageTypeId'   => 'int',
        'packageTypeName' => 'string',
        'deliveryTypes'   => DeliveryTypeCollection::class,
        'shipmentOptions' => ShipmentOptions::class,
    ];
}
