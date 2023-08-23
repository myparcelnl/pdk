<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

use MyParcelNL\Pdk\Types\Service\TriStateService;

/**
 * @property int<-1>|string $countryOfOrigin
 * @property int<-1>|string $customsCode
 * @property int<-1|0|1>    $disableDeliveryOptions
 * @property int            $dropOffDelay
 * @property int<-1|0|1>    $exportAgeCheck
 * @property int<-1|0|1>    $exportHideSender
 * @property int<-1|0|1>    $exportInsurance
 * @property int<-1|0|1>    $exportLargeFormat
 * @property int<-1|0|1>    $exportOnlyRecipient
 * @property int<-1|0|1>    $exportReturn
 * @property int<-1|0|1>    $exportSignature
 * @property int            $fitInDigitalStamp
 * @property int            $fitInMailbox
 * @property int<-1>|string $packageType
 */
class ProductSettings extends AbstractSettingsModel
{
    public const ID                       = 'product';
    public const COUNTRY_OF_ORIGIN        = 'countryOfOrigin';
    public const CUSTOMS_CODE             = 'customsCode';
    public const DISABLE_DELIVERY_OPTIONS = 'disableDeliveryOptions';
    public const DROP_OFF_DELAY           = 'dropOffDelay';
    public const EXPORT_AGE_CHECK         = 'exportAgeCheck';
    public const EXPORT_HIDE_SENDER       = 'exportHideSender';
    public const EXPORT_INSURANCE         = 'exportInsurance';
    public const EXPORT_LARGE_FORMAT      = 'exportLargeFormat';
    public const EXPORT_ONLY_RECIPIENT    = 'exportOnlyRecipient';
    public const EXPORT_RETURN            = 'exportReturn';
    public const EXPORT_SIGNATURE         = 'exportSignature';
    public const FIT_IN_DIGITAL_STAMP     = 'fitInDigitalStamp';
    public const FIT_IN_MAILBOX           = 'fitInMailbox';
    public const PACKAGE_TYPE             = 'packageType';

    protected $attributes = [
        'id' => self::ID,

        self::COUNTRY_OF_ORIGIN        => TriStateService::INHERIT,
        self::CUSTOMS_CODE             => TriStateService::INHERIT,
        self::DISABLE_DELIVERY_OPTIONS => TriStateService::INHERIT,
        self::DROP_OFF_DELAY           => TriStateService::INHERIT,
        self::EXPORT_AGE_CHECK         => TriStateService::INHERIT,
        self::EXPORT_HIDE_SENDER       => TriStateService::INHERIT,
        self::EXPORT_INSURANCE         => TriStateService::INHERIT,
        self::EXPORT_LARGE_FORMAT      => TriStateService::INHERIT,
        self::EXPORT_ONLY_RECIPIENT    => TriStateService::INHERIT,
        self::EXPORT_RETURN            => TriStateService::INHERIT,
        self::EXPORT_SIGNATURE         => TriStateService::INHERIT,
        self::FIT_IN_DIGITAL_STAMP     => TriStateService::INHERIT,
        self::FIT_IN_MAILBOX           => TriStateService::INHERIT,
        self::PACKAGE_TYPE             => TriStateService::INHERIT,
    ];

    protected $casts      = [
        self::COUNTRY_OF_ORIGIN        => TriStateService::TYPE_COERCED,
        self::CUSTOMS_CODE             => TriStateService::TYPE_COERCED,
        self::DISABLE_DELIVERY_OPTIONS => TriStateService::TYPE_STRICT,
        self::EXPORT_AGE_CHECK         => TriStateService::TYPE_STRICT,
        self::EXPORT_HIDE_SENDER       => TriStateService::TYPE_STRICT,
        self::EXPORT_INSURANCE         => TriStateService::TYPE_STRICT,
        self::EXPORT_LARGE_FORMAT      => TriStateService::TYPE_STRICT,
        self::EXPORT_ONLY_RECIPIENT    => TriStateService::TYPE_STRICT,
        self::EXPORT_RETURN            => TriStateService::TYPE_STRICT,
        self::EXPORT_SIGNATURE         => TriStateService::TYPE_STRICT,
        self::PACKAGE_TYPE             => TriStateService::TYPE_COERCED,
    ];
}
