<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Model;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property bool   $age_check
 * @property bool   $collect
 * @property bool   $cooled_delivery
 * @property string $delivery_date
 * @property int    $delivery_type
 * @property bool   $drop_off_at_postal_point
 * @property bool   $extra_assurance
 * @property bool   $hide_sender
 * @property int    $insurance
 * @property bool   $label_description
 * @property bool   $large_format
 * @property bool   $only_recipient
 * @property int    $package_type
 * @property bool   $return
 * @property bool   $same_day_delivery
 * @property bool   $saturday_delivery
 * @property bool   $signature
 */
class ShipmentOptions extends Model
{
    public $attributes = [
        'age_check'                => null,
        'collect'                  => null,
        'cooled_delivery'          => null,
        'delivery_date'            => null,
        'delivery_type'            => null,
        'drop_off_at_postal_point' => null,
        'extra_assurance'          => null,
        'hide_sender'              => null,
        'insurance'                => null,
        'label_description'        => null,
        'large_format'             => null,
        'only_recipient'           => null,
        'package_type'             => null,
        'return'                   => null,
        'same_day_delivery'        => null,
        'saturday_delivery'        => null,
        'signature'                => null,
    ];

    public $casts      = [
        'age_check'                => 'bool',
        'collect'                  => 'bool',
        'cooled_delivery'          => 'bool',
        'delivery_date'            => 'string',
        'delivery_type'            => 'int',
        'drop_off_at_postal_point' => 'bool',
        'extra_assurance'          => 'bool',
        'hide_sender'              => 'bool',
        'insurance'                => 'int',
        'label_description'        => 'bool',
        'large_format'             => 'bool',
        'only_recipient'           => 'bool',
        'package_type'             => 'int',
        'return'                   => 'bool',
        'same_day_delivery'        => 'bool',
        'saturday_delivery'        => 'bool',
        'signature'                => 'bool',
    ];
}
