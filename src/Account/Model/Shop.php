<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Model;

use MyParcelNL\Pdk\Account\Collection\ShopCarrierConfigurationCollection;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Carrier\Collection\CarrierOptionsCollection;

/**
 * @property int                                $id
 * @property int                                $accountId
 * @property int                                $platformId
 * @property string                             $name
 * @property bool                               $hidden
 * @property array                              $billing
 * @property array                              $deliveryAddress
 * @property array                              $generalSettings
 * @property array                              $return
 * @property array                              $shipmentOptions
 * @property array                              $trackTrace
 * @property ShopCarrierConfigurationCollection $carrierConfigurations
 * @property CarrierOptionsCollection           $carrierOptions
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
        'carrierConfigurations' => ShopCarrierConfigurationCollection::class,
        'carrierOptions'        => CarrierOptionsCollection::class,
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
        'carrierConfigurations' => ShopCarrierConfigurationCollection::class,
        'carrierOptions'        => CarrierOptionsCollection::class,
    ];
}
