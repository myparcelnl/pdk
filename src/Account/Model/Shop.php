<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Model;

use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;

/**
 * @property int                                $id
 * @property int                                $accountId
 * @property int                                $platformId
 * @property string                             $name
 * @property bool                               $hidden
 * @property array<string, mixed>               $billing
 * @property array<string, mixed>               $deliveryAddress
 * @property array<string, mixed>               $generalSettings
 * @property array<string, mixed>               $return
 * @property array<string, mixed>               $shipmentOptions
 * @property array<string, mixed>[]             $trackTrace
 * @property CarrierCollection                  $carriers
 */
class Shop extends Model
{
    public    $attributes = [
        'id'                    => null,
        'accountId'             => null,
        'platformId'            => null,
        'name'                  => null,
        'hidden'                => false,
        'billing'               => [],
        'deliveryAddress'       => [],
        'generalSettings'       => [],
        'return'                => [],
        'shipmentOptions'       => [],
        'trackTrace'            => [],
        'carriers'              => CarrierCollection::class,
    ];

    protected $casts      = [
        'id'                    => 'int',
        'accountId'             => 'int',
        'platformId'            => 'int',
        'name'                  => 'string',
        'hidden'                => 'bool',
        'billing'               => 'array',
        'deliveryAddress'       => 'array',
        'generalSettings'       => 'array',
        'return'                => 'array',
        'shipmentOptions'       => 'array',
        'trackTrace'            => 'array',
        'carriers'              => CarrierCollection::class,
    ];
}
