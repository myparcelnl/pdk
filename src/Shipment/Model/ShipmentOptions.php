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
 * @property int<-1|0|1>         $receiptCode
 * @property int<-1|0|1>         $return
 * @property int<-1|0|1>         $sameDayDelivery
 * @property int<-1|0|1>         $signature
 * @property int<-1|0|1>         $tracked
 * @property int<-1|0|1>         $collect
 * @property int<-1|0|1>         $excludeParcelLockers
 * @property int<-1|0|1>         $freshFood
 * @property int<-1|0|1>         $frozen
 * @property int<-1|0|1>         $priority_delivery
 */
class ShipmentOptions extends Model
{
    public const LABEL_DESCRIPTION = 'labelDescription';
    public const INSURANCE         = 'insurance';
    public const AGE_CHECK         = 'ageCheck';
    public const DIRECT_RETURN     = 'return';
    public const HIDE_SENDER       = 'hideSender';
    public const LARGE_FORMAT      = 'largeFormat';
    public const ONLY_RECIPIENT    = 'onlyRecipient';
    public const RECEIPT_CODE      = 'receiptCode';
    public const SAME_DAY_DELIVERY = 'sameDayDelivery';
    public const SIGNATURE         = 'signature';
    public const TRACKED           = 'tracked';
    public const COLLECT           = 'collect';
    public const EXCLUDE_PARCEL_LOCKERS = 'excludeParcelLockers';
    public const FRESH_FOOD        = 'freshFood';
    public const FROZEN            = 'frozen';
    public const PRIORITY          = 'priorityDelivery';

    protected $attributes = [
        self::LABEL_DESCRIPTION => null,
        self::INSURANCE         => TriStateService::INHERIT,
        self::AGE_CHECK         => TriStateService::INHERIT,
        self::DIRECT_RETURN     => TriStateService::INHERIT,
        self::HIDE_SENDER       => TriStateService::INHERIT,
        self::LARGE_FORMAT      => TriStateService::INHERIT,
        self::ONLY_RECIPIENT    => TriStateService::INHERIT,
        self::RECEIPT_CODE      => TriStateService::INHERIT,
        self::SAME_DAY_DELIVERY => TriStateService::INHERIT,
        self::SIGNATURE         => TriStateService::INHERIT,
        self::TRACKED           => TriStateService::INHERIT,
        self::COLLECT           => TriStateService::INHERIT,
        self::EXCLUDE_PARCEL_LOCKERS => TriStateService::INHERIT,
        self::FRESH_FOOD        => TriStateService::INHERIT,
        self::FROZEN            => TriStateService::INHERIT,
        self::PRIORITY          => TriStateService::INHERIT,
    ];

    protected $casts      = [
        self::LABEL_DESCRIPTION => TriStateService::TYPE_STRING,
        self::INSURANCE         => 'int',
        self::AGE_CHECK         => TriStateService::TYPE_STRICT,
        self::DIRECT_RETURN     => TriStateService::TYPE_STRICT,
        self::HIDE_SENDER       => TriStateService::TYPE_STRICT,
        self::LARGE_FORMAT      => TriStateService::TYPE_STRICT,
        self::ONLY_RECIPIENT    => TriStateService::TYPE_STRICT,
        self::RECEIPT_CODE      => TriStateService::TYPE_STRICT,
        self::SAME_DAY_DELIVERY => TriStateService::TYPE_STRICT,
        self::SIGNATURE         => TriStateService::TYPE_STRICT,
        self::TRACKED           => TriStateService::TYPE_STRICT,
        self::COLLECT           => TriStateService::TYPE_STRICT,
        self::EXCLUDE_PARCEL_LOCKERS => TriStateService::TYPE_STRICT,
        self::FRESH_FOOD        => TriStateService::TYPE_STRICT,
        self::FROZEN            => TriStateService::TYPE_STRICT,
        self::PRIORITY          => TriStateService::TYPE_STRICT,
    ];
}
