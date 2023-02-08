<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Model;

use MyParcelNL\Pdk\Base\Model\Address;
use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property bool    $hasDeliveryOptions
 * @property int     $minimumDropOffDelay
 * @property string  $preferPackageType
 * @property array   $allowPackageTypes
 * @property Address $shippingAddress
 */
class PdkShippingMethod extends Model
{
    protected $attributes = [
        'hasDeliveryOptions'  => true,
        'minimumDropOffDelay' => null,
        'preferPackageType'   => null,
        'allowPackageTypes'   => [],
        'shippingAddress'     => Address::class,
    ];

    protected $casts      = [
        'hasDeliveryOptions'  => 'bool',
        'minimumDropOffDelay' => 'int',
        'preferPackageType'   => 'string',
        'allowPackageTypes'   => 'array',
        'shippingAddress'     => Address::class,
    ];
}
