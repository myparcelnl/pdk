<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Model;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Carrier\Collection\CarrierOptionsCollection;

/**
 * @property null|int                                                    $id
 * @property null|string                                                 $name
 * @property null|string                                                 $human
 * @property null|int                                                    $subscriptionId
 * @property null|bool                                                   $primary
 * @property null|bool                                                   $isDefault
 * @property null|bool                                                   $optional
 * @property null|string                                                 $label
 * @property null|string                                                 $type
 * @property \MyParcelNL\Pdk\Carrier\Collection\CarrierOptionsCollection $options
 * @property \MyParcelNL\Pdk\Carrier\Collection\CarrierOptionsCollection $returnOptions
 */
class Carrier extends Model
{
    public const         CARRIER_POSTNL_ID     = 1;
    public const         CARRIER_BPOST_ID      = 2;
    public const         CARRIER_DPD_ID        = 4;
    public const         CARRIER_INSTABOX_ID   = 5;
    public const         CARRIER_POSTNL_NAME   = 'postnl';
    public const         CARRIER_BPOST_NAME    = 'bpost';
    public const         CARRIER_DPD_NAME      = 'dpd';
    public const         CARRIER_INSTABOX_NAME = 'instabox';
    public const         TYPE_NAME             = 'type';
    public const         TYPE_VALUE_CUSTOM     = 'custom';
    public const         TYPE_VALUE_MAIN       = 'main';

    protected $attributes = [
        'id'             => null,
        'name'           => null,
        'human'          => null,
        'subscriptionId' => null,
        'primary'        => null,
        'isDefault'      => null,
        'optional'       => null,
        'label'          => null,
        'type'           => null,
        'options'        => CarrierOptionsCollection::class,
        'returnOptions'  => CarrierOptionsCollection::class,
    ];

    protected $casts      = [
        'id'             => 'int',
        'name'           => 'string',
        'human'          => 'string',
        'subscriptionId' => 'int',
        'primary'        => 'bool',
        'isDefault'      => 'bool',
        'optional'       => 'bool',
        'label'          => 'string',
        'type'           => 'string',
        'options'        => CarrierOptionsCollection::class,
        'returnOptions'  => CarrierOptionsCollection::class,
    ];
}
