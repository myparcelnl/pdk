<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model\Options;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property string cc
 * @property string city
 * @property string locationCode
 * @property string locationName
 * @property string number
 * @property string postalCode
 * @property string retailNetworkId
 * @property string street
 */
class PickupLocation extends Model
{
    protected $attributes = [
        'cc'              => 'string',
        'city'            => 'string',
        'locationCode'    => 'string',
        'locationName'    => 'string',
        'number'          => 'string',
        'postalCode'      => 'string',
        'retailNetworkId' => 'string',
        'street'          => 'string',
    ];
}
