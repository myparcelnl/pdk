<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Model;

use MyParcelNL\Pdk\Base\Model\Address;
use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property null|string                             $packageType
 * @property null|\MyParcelNL\Pdk\Base\Model\Address $shippingAddress
 */
class PdkShippingMethod extends Model
{
    protected $attributes = [
        'disableDeliveryOptions' => false,
        'minimumDropOffDelay'    => null,
        'preferPackageType'      => null,
        'allowPackageTypes'      => null,
        'shippingAddress'        => null,
    ];

    protected $casts      = [
        'disableDeliveryOptions' => 'bool',
        'minimumDropOffDelay'    => 'int',
        'preferPackageType'      => 'string',
        'allowPackageTypes'      => 'array',
        'shippingAddress'        => Address::class,
    ];
}
