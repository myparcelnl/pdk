<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property bool|null   $ageCheck
 * @property int|null    $insurance
 * @property string|null $labelDescription
 * @property bool|null   $hideSender
 * @property bool|null   $largeFormat
 * @property bool|null   $onlyRecipient
 * @property bool|null   $return
 * @property bool|null   $sameDayDelivery
 * @property bool|null   $signature
 */
class ShipmentOptions extends Model
{
    public const AGE_CHECK         = 'ageCheck';
    public const HIDE_SENDER       = 'hideSender';
    public const INSURANCE         = 'insurance';
    public const LABEL_DESCRIPTION = 'labelDescription';
    public const LARGE_FORMAT      = 'largeFormat';
    public const ONLY_RECIPIENT    = 'onlyRecipient';
    public const RETURN            = 'return';
    public const SAME_DAY_DELIVERY = 'sameDayDelivery';
    public const SIGNATURE         = 'signature';

    protected $attributes = [
        self::AGE_CHECK         => null,
        self::HIDE_SENDER       => null,
        self::INSURANCE         => null,
        self::LABEL_DESCRIPTION => null,
        self::LARGE_FORMAT      => null,
        self::ONLY_RECIPIENT    => null,
        self::RETURN            => null,
        self::SAME_DAY_DELIVERY => null,
        self::SIGNATURE         => null,
    ];

    protected $casts      = [
        self::AGE_CHECK         => 'bool',
        self::HIDE_SENDER       => 'bool',
        self::INSURANCE         => 'int',
        self::LABEL_DESCRIPTION => 'string',
        self::LARGE_FORMAT      => 'bool',
        self::ONLY_RECIPIENT    => 'bool',
        self::RETURN            => 'bool',
        self::SAME_DAY_DELIVERY => 'bool',
        self::SIGNATURE         => 'bool',
    ];
}
