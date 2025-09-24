<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Model;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property string[] $deliveryTypes
 * @property array    $features
 * @property string[] $packageTypes
 * @property array    $shipmentOptions
 */
class CarrierCapabilities extends Model
{
    protected $attributes = [
        'deliveryTypes'   => [],
        'features'        => [],
        'packageTypes'    => [],
        'shipmentOptions' => [],
    ];

    protected $casts      = [
        'deliveryTypes'   => 'array',
        'features'        => 'array',
        'packageTypes'    => 'array',
        'shipmentOptions' => 'array',
    ];
}
