<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Model\Options;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Carrier\Collection\DeliveryTypeCollection;
use MyParcelNL\Pdk\Shipment\Model\Options\ShipmentOptions;

class PackageType extends Model
{
    protected $attributes = [
        'id'                 => null,
        'name'               => null,
        'deliveryTypes'      => DeliveryTypeCollection::class,
        'packageTypeOptions' => ShipmentOptions::class,
    ];

    protected $casts      = [
        'id'                 => 'int',
        'name'               => 'string',
        'deliveryTypes'      => DeliveryTypeCollection::class,
        'packageTypeOptions' => ShipmentOptions::class,
    ];
}
