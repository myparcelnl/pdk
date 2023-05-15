<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\ShippingMethod\Model;

use MyParcelNL\Pdk\Base\Model\Address;
use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property string  $id
 * @property string  $name
 * @property bool    $isEnabled
 * @property bool    $hasDeliveryOptions
 * @property int     $minimumDropOffDelay
 * @property array   $allowPackageTypes
 * @property Address $shippingAddress
 */
class PdkShippingMethod extends Model
{
    protected $attributes = [
        'id'                  => null,
        'name'                => null,
        'allowPackageTypes'   => [],
        'hasDeliveryOptions'  => true,
        'isEnabled'           => true,
        'minimumDropOffDelay' => null,
        'shippingAddress'     => Address::class,
    ];

    protected $casts      = [
        'id'                  => 'string',
        'name'                => 'string',
        'allowPackageTypes'   => 'array',
        'hasDeliveryOptions'  => 'bool',
        'isEnabled'           => 'bool',
        'minimumDropOffDelay' => 'int',
        'shippingAddress'     => Address::class,
    ];
}
