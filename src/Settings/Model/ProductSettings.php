<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Frontend\Settings\View\AbstractSettingsView;
use MyParcelNL\Pdk\Shipment\Model\CustomsDeclarationItem;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;

/**
 * @property int<-1, 1> $allowOnlyRecipient
 * @property int<-1, 1> $allowSignature
 * @property string     $countryOfOrigin
 * @property string     $customsCode
 * @property int<-1, 1> $disableDeliveryOptions
 * @property int        $dropOffDelay
 * @property int<-1, 1> $exportAgeCheck
 * @property int<-1, 1> $exportInsurance
 * @property int<-1, 1> $exportLargeFormat
 * @property int        $fitInMailbox
 * @property string     $packageType
 * @property int<-1, 1> $returnShipments
 */
class ProductSettings extends AbstractSettingsModel
{
    public const ID                       = 'product';
    public const ALLOW_ONLY_RECIPIENT     = 'allowOnlyRecipient';
    public const ALLOW_SIGNATURE          = 'allowSignature';
    public const COUNTRY_OF_ORIGIN        = 'countryOfOrigin';
    public const CUSTOMS_CODE             = 'customsCode';
    public const DISABLE_DELIVERY_OPTIONS = 'disableDeliveryOptions';
    public const DROP_OFF_DELAY           = 'dropOffDelay';
    public const EXPORT_AGE_CHECK         = 'exportAgeCheck';
    public const EXPORT_INSURANCE         = 'exportInsurance';
    public const EXPORT_LARGE_FORMAT      = 'exportLargeFormat';
    public const FIT_IN_MAILBOX           = 'fitInMailbox';
    public const PACKAGE_TYPE             = 'packageType';
    public const RETURN_SHIPMENTS         = 'returnShipments';

    protected $attributes = [
        'id' => self::ID,

        self::ALLOW_ONLY_RECIPIENT     => AbstractSettingsView::TRISTATE_VALUE_DEFAULT,
        self::ALLOW_SIGNATURE          => AbstractSettingsView::TRISTATE_VALUE_DEFAULT,
        self::COUNTRY_OF_ORIGIN        => CountryCodes::CC_NL,
        self::CUSTOMS_CODE             => CustomsDeclarationItem::DEFAULT_CLASSIFICATION,
        self::DISABLE_DELIVERY_OPTIONS => AbstractSettingsView::TRISTATE_VALUE_DEFAULT,
        self::DROP_OFF_DELAY           => 0,
        self::EXPORT_AGE_CHECK         => AbstractSettingsView::TRISTATE_VALUE_DEFAULT,
        self::EXPORT_INSURANCE         => AbstractSettingsView::TRISTATE_VALUE_DEFAULT,
        self::EXPORT_LARGE_FORMAT      => AbstractSettingsView::TRISTATE_VALUE_DEFAULT,
        self::FIT_IN_MAILBOX           => 0,
        self::PACKAGE_TYPE             => DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME,
        self::RETURN_SHIPMENTS         => AbstractSettingsView::TRISTATE_VALUE_DEFAULT,
    ];

    protected $casts      = [
        self::ALLOW_ONLY_RECIPIENT     => 'int',
        self::ALLOW_SIGNATURE          => 'int',
        self::COUNTRY_OF_ORIGIN        => 'string',
        self::CUSTOMS_CODE             => 'string',
        self::DISABLE_DELIVERY_OPTIONS => 'int',
        self::DROP_OFF_DELAY           => 'int',
        self::EXPORT_AGE_CHECK         => 'int',
        self::EXPORT_INSURANCE         => 'int',
        self::EXPORT_LARGE_FORMAT      => 'int',
        self::FIT_IN_MAILBOX           => 'int',
        self::PACKAGE_TYPE             => 'string',
        self::RETURN_SHIPMENTS         => 'int',
    ];
}
