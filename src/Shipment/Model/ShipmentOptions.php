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
 */
class ShipmentOptions extends Model
{
    public const LABEL_DESCRIPTION = 'LABEL_DESCRIPTION';
    public const INSURANCE         = 'INSURANCE';
    public const AGE_CHECK         = 'AGE_CHECK';
    public const DIRECT_RETURN     = 'RETURN';
    public const HIDE_SENDER       = 'HIDE_SENDER';
    public const LARGE_FORMAT      = 'LARGE_FORMAT';
    public const ONLY_RECIPIENT    = 'ONLY_RECIPIENT';
    public const RECEIPT_CODE      = 'RECEIPT_CODE';
    public const SAME_DAY_DELIVERY = 'SAME_DAY_DELIVERY';
    public const SIGNATURE         = 'SIGNATURE';
    public const TRACKED           = 'TRACKED';
    public const COLLECT           = 'COLLECT';

    /**
     * @deprecated Use the corresponding non-LEGACY constants instead
     */
    public const LABEL_DESCRIPTION_LEGACY = 'labelDescription';
    /**
     * @deprecated Use INSURANCE instead
     */
    public const INSURANCE_LEGACY         = 'insurance';
    /**
     * @deprecated Use AGE_CHECK instead
     */
    public const AGE_CHECK_LEGACY         = 'ageCheck';
    /**
     * @deprecated Use DIRECT_RETURN instead
     */
    public const DIRECT_RETURN_LEGACY     = 'return';
    /**
     * @deprecated Use HIDE_SENDER instead
     */
    public const HIDE_SENDER_LEGACY       = 'hideSender';
    /**
     * @deprecated Use LARGE_FORMAT instead
     */
    public const LARGE_FORMAT_LEGACY      = 'largeFormat';
    /**
     * @deprecated Use ONLY_RECIPIENT instead
     */
    public const ONLY_RECIPIENT_LEGACY    = 'onlyRecipient';
    /**
     * @deprecated Use RECEIPT_CODE instead
     */
    public const RECEIPT_CODE_LEGACY      = 'receiptCode';
    /**
     * @deprecated Use SAME_DAY_DELIVERY instead
     */
    public const SAME_DAY_DELIVERY_LEGACY = 'sameDayDelivery';
    /**
     * @deprecated Use SIGNATURE instead
     */
    public const SIGNATURE_LEGACY         = 'signature';
    /**
     * @deprecated Use TRACKED instead
     */
    public const TRACKED_LEGACY           = 'tracked';
    /**
     * @deprecated Use COLLECT instead
     */
    public const COLLECT_LEGACY           = 'collect';

    public const SHIPMENT_OPTION_NAMES = [
        self::LABEL_DESCRIPTION,
        self::INSURANCE,
        self::AGE_CHECK,
        self::DIRECT_RETURN,
        self::HIDE_SENDER,
        self::LARGE_FORMAT,
        self::ONLY_RECIPIENT,
        self::RECEIPT_CODE,
        self::SAME_DAY_DELIVERY,
        self::SIGNATURE,
        self::TRACKED,
        self::COLLECT
    ];

    /**
     * @deprecated Use new shipment option names directly
     */
    public const SHIPMENT_OPTION_NAME_TO_LEGACY_MAP = [
        self::LABEL_DESCRIPTION => self::LABEL_DESCRIPTION_LEGACY,
        self::INSURANCE         => self::INSURANCE_LEGACY,
        self::AGE_CHECK         => self::AGE_CHECK_LEGACY,
        self::DIRECT_RETURN     => self::DIRECT_RETURN_LEGACY,
        self::HIDE_SENDER       => self::HIDE_SENDER_LEGACY,
        self::LARGE_FORMAT      => self::LARGE_FORMAT_LEGACY,
        self::ONLY_RECIPIENT    => self::ONLY_RECIPIENT_LEGACY,
        self::RECEIPT_CODE      => self::RECEIPT_CODE_LEGACY,
        self::SAME_DAY_DELIVERY => self::SAME_DAY_DELIVERY_LEGACY,
        self::SIGNATURE         => self::SIGNATURE_LEGACY,
        self::TRACKED           => self::TRACKED_LEGACY,
        self::COLLECT           => self::COLLECT_LEGACY,
    ];

    protected $attributes = [
        self::LABEL_DESCRIPTION_LEGACY => null,
        self::INSURANCE_LEGACY         => TriStateService::INHERIT,
        self::AGE_CHECK_LEGACY         => TriStateService::INHERIT,
        self::DIRECT_RETURN_LEGACY     => TriStateService::INHERIT,
        self::HIDE_SENDER_LEGACY       => TriStateService::INHERIT,
        self::LARGE_FORMAT_LEGACY      => TriStateService::INHERIT,
        self::ONLY_RECIPIENT_LEGACY    => TriStateService::INHERIT,
        self::RECEIPT_CODE_LEGACY      => TriStateService::INHERIT,
        self::SAME_DAY_DELIVERY_LEGACY => TriStateService::INHERIT,
        self::SIGNATURE_LEGACY         => TriStateService::INHERIT,
        self::TRACKED_LEGACY           => TriStateService::INHERIT,
        self::COLLECT_LEGACY           => TriStateService::INHERIT,
    ];

    protected $casts      = [
        self::LABEL_DESCRIPTION_LEGACY => TriStateService::TYPE_STRING,
        self::INSURANCE_LEGACY         => 'int',
        self::AGE_CHECK_LEGACY         => TriStateService::TYPE_STRICT,
        self::DIRECT_RETURN_LEGACY     => TriStateService::TYPE_STRICT,
        self::HIDE_SENDER_LEGACY       => TriStateService::TYPE_STRICT,
        self::LARGE_FORMAT_LEGACY      => TriStateService::TYPE_STRICT,
        self::ONLY_RECIPIENT_LEGACY    => TriStateService::TYPE_STRICT,
        self::RECEIPT_CODE_LEGACY      => TriStateService::TYPE_STRICT,
        self::SAME_DAY_DELIVERY_LEGACY => TriStateService::TYPE_STRICT,
        self::SIGNATURE_LEGACY         => TriStateService::TYPE_STRICT,
        self::TRACKED_LEGACY           => TriStateService::TYPE_STRICT,
        self::COLLECT_LEGACY           => TriStateService::TYPE_STRICT,
    ];
}
