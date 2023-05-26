<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property string|null $locationCode
 * @property string|null $locationName
 * @property string|null $retailNetworkId
 * @property null|string $boxNumber
 * @property null|string $cc
 * @property null|string $city
 * @property null|string $number
 * @property null|string $numberSuffix
 * @property null|string $postalCode
 * @property null|string $region
 * @property null|string $state
 * @property null|string $street
 */
class RetailLocation extends Model
{
    protected $attributes = [
        'locationCode'    => null,
        'locationName'    => null,
        'retailNetworkId' => null,
        'boxNumber'       => null,
        'cc'              => null,
        'city'            => null,
        'number'          => null,
        'numberSuffix'    => null,
        'postalCode'      => null,
        'region'          => null,
        'state'           => null,
        'street'          => null,
    ];

    protected $casts      = [
        'locationCode'    => 'string',
        'locationName'    => 'string',
        'retailNetworkId' => 'string',
        'boxNumber'       => 'string',
        'cc'              => 'string',
        'city'            => 'string',
        'number'          => 'string',
        'numberSuffix'    => 'string',
        'postalCode'      => 'string',
        'region'          => 'string',
        'state'           => 'string',
        'street'          => 'string',
    ];
}
