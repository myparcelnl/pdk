<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\ShippingMethod\Model;

use MyParcelNL\Pdk\Base\Model\Address;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Shipment\Collection\PackageTypeCollection;

/**
 * @property string                $id
 * @property string                $name
 * @property string                $description
 * @property bool                  $isEnabled
 * @property bool                  $hasDeliveryOptions
 * @property int                   $minimumDropOffDelay
 * @property PackageTypeCollection $allowedPackageTypes
 * @property Address               $shippingAddress
 */
class PdkShippingMethod extends Model
{
    protected $attributes = [
        'id'                  => null,
        'name'                => null,
        'description'         => null,
        'allowedPackageTypes' => PackageTypeCollection::class,
        'hasDeliveryOptions'  => true,
        'isEnabled'           => true,
        'minimumDropOffDelay' => null,
        'shippingAddress'     => Address::class,
    ];

    protected $casts      = [
        'id'                  => 'string',
        'name'                => 'string',
        'description'         => 'string',
        'allowedPackageTypes' => PackageTypeCollection::class,
        'hasDeliveryOptions'  => 'bool',
        'isEnabled'           => 'bool',
        'minimumDropOffDelay' => 'int',
        'shippingAddress'     => Address::class,
    ];

    protected $deprecated = [
        'allowPackageTypes' => 'allowedPackageTypes',
    ];
}
