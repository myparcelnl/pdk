<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Types\Service\TriStateService;

/**
 * @property int<-1>|string|null $labelDescription
 * @property int                 $insurance
 * @property int<-1|0|1>         $ageCheck
 * @property int<-1|0|1>         $hideSender
 * @property int<-1|0|1>         $largeFormat
 * @property int<-1|0|1>         $onlyRecipient
 * @property int<-1|0|1>         $return
 * @property int<-1|0|1>         $sameDayDelivery
 * @property int<-1|0|1>         $signature
 */
class ShipmentOptions extends Model
{
    final public const AGE_CHECK         = 'ageCheck';
    final public const HIDE_SENDER       = 'hideSender';
    final public const INSURANCE         = 'insurance';
    final public const LABEL_DESCRIPTION = 'labelDescription';
    final public const LARGE_FORMAT      = 'largeFormat';
    final public const ONLY_RECIPIENT    = 'onlyRecipient';
    final public const DIRECT_RETURN     = 'return';
    final public const SAME_DAY_DELIVERY = 'sameDayDelivery';
    final public const SIGNATURE         = 'signature';

    protected $attributes = [
        self::LABEL_DESCRIPTION => null,
        self::INSURANCE         => TriStateService::INHERIT,
        self::AGE_CHECK         => TriStateService::INHERIT,
        self::HIDE_SENDER       => TriStateService::INHERIT,
        self::LARGE_FORMAT      => TriStateService::INHERIT,
        self::ONLY_RECIPIENT    => TriStateService::INHERIT,
        self::DIRECT_RETURN     => TriStateService::INHERIT,
        self::SAME_DAY_DELIVERY => TriStateService::INHERIT,
        self::SIGNATURE         => TriStateService::INHERIT,
    ];

    protected $casts      = [
        self::LABEL_DESCRIPTION => TriStateService::TYPE_STRING,
        self::INSURANCE         => 'int',
        self::AGE_CHECK         => TriStateService::TYPE_STRICT,
        self::HIDE_SENDER       => TriStateService::TYPE_STRICT,
        self::LARGE_FORMAT      => TriStateService::TYPE_STRICT,
        self::ONLY_RECIPIENT    => TriStateService::TYPE_STRICT,
        self::DIRECT_RETURN     => TriStateService::TYPE_STRICT,
        self::SAME_DAY_DELIVERY => TriStateService::TYPE_STRICT,
        self::SIGNATURE         => TriStateService::TYPE_STRICT,
    ];
}
