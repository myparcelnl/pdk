<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Shipment\Model\CustomsDeclarationItem;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;

/**
 * @property string     $countryOfOrigin
 * @property string     $customsCode
 * @property bool       $disableDeliveryOptions
 * @property int        $dropOffDelay
 * @property int<-1, 1> $exportAgeCheck
 * @property int<-1, 1> $exportHideSender
 * @property int<-1, 1> $exportInsurance
 * @property int<-1, 1> $exportLargeFormat
 * @property int<-1, 1> $exportOnlyRecipient
 * @property int<-1, 1> $exportSignature
 * @property int        $fitInDigitalStamp
 * @property int        $fitInMailbox
 * @property string     $packageType
 * @property int<-1, 1> $returnShipments
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
    public const FIT_IN_MAILBOX           = 'fitInMailbox';
    public const FIT_IN_DIGITAL_STAMP     = 'fitInDigitalStamp';
    public const PACKAGE_TYPE             = 'packageType';

    protected $attributes = [
        'id' => self::ID,

        self::COUNTRY_OF_ORIGIN        => CountryCodes::CC_NL,
        self::CUSTOMS_CODE             => CustomsDeclarationItem::DEFAULT_CLASSIFICATION,
        self::DISABLE_DELIVERY_OPTIONS => false,
        self::DROP_OFF_DELAY           => 0,
        self::EXPORT_AGE_CHECK         => AbstractSettingsModel::TRISTATE_VALUE_DEFAULT,
        self::EXPORT_HIDE_SENDER       => AbstractSettingsModel::TRISTATE_VALUE_DEFAULT,
        self::EXPORT_INSURANCE         => AbstractSettingsModel::TRISTATE_VALUE_DEFAULT,
        self::EXPORT_LARGE_FORMAT      => AbstractSettingsModel::TRISTATE_VALUE_DEFAULT,
        self::EXPORT_ONLY_RECIPIENT    => AbstractSettingsModel::TRISTATE_VALUE_DEFAULT,
        self::EXPORT_RETURN            => AbstractSettingsModel::TRISTATE_VALUE_DEFAULT,
        self::EXPORT_SIGNATURE         => AbstractSettingsModel::TRISTATE_VALUE_DEFAULT,
        self::FIT_IN_MAILBOX           => 0,
        self::FIT_IN_DIGITAL_STAMP     => 0,
        self::PACKAGE_TYPE             => DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME,
    ];

    protected $casts      = [
        self::COUNTRY_OF_ORIGIN        => 'string',
        self::CUSTOMS_CODE             => 'string',
        self::DISABLE_DELIVERY_OPTIONS => 'boolean',
        self::DROP_OFF_DELAY           => 'int',
        self::EXPORT_AGE_CHECK         => 'int',
        self::EXPORT_HIDE_SENDER       => 'int',
        self::EXPORT_INSURANCE         => 'int',
        self::EXPORT_LARGE_FORMAT      => 'int',
        self::EXPORT_ONLY_RECIPIENT    => 'int',
        self::EXPORT_RETURN            => 'int',
        self::EXPORT_SIGNATURE         => 'int',
        self::FIT_IN_MAILBOX           => 'int',
        self::FIT_IN_DIGITAL_STAMP     => 'int',
        self::PACKAGE_TYPE             => 'string',
    ];
}
