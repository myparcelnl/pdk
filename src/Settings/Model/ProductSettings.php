<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Shipment\Model\CustomsDeclarationItem;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;

/**
 * @property bool   $allowOnlyRecipient
 * @property bool   $allowSignature
 * @property string $countryOfOrigin
 * @property string $customsCode
 * @property bool   $disableDeliveryOptions
 * @property int    $dropOffDelay
 * @property bool   $exportAgeCheck
 * @property bool   $exportInsurance
 * @property bool   $exportLargeFormat
 * @property int    $fitInMailbox
 * @property string $packageType
 * @property bool   $returnShipments
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

        self::ALLOW_ONLY_RECIPIENT     => -1,
        self::ALLOW_SIGNATURE          => -1,
        self::COUNTRY_OF_ORIGIN        => CountryCodes::CC_NL,
        self::CUSTOMS_CODE             => CustomsDeclarationItem::DEFAULT_CLASSIFICATION,
        self::DISABLE_DELIVERY_OPTIONS => -1,
        self::DROP_OFF_DELAY           => 0,
        self::EXPORT_AGE_CHECK         => -1,
        self::EXPORT_INSURANCE         => -1,
        self::EXPORT_LARGE_FORMAT      => -1,
        self::FIT_IN_MAILBOX           => 0,
        self::PACKAGE_TYPE             => DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME,
        self::RETURN_SHIPMENTS         => -1,
    ];

    protected $casts      = [
        self::ALLOW_ONLY_RECIPIENT     => 'integer', //tristate
        self::ALLOW_SIGNATURE          => 'integer', //tristate
        self::COUNTRY_OF_ORIGIN        => 'string',
        self::CUSTOMS_CODE             => 'string',
        self::DISABLE_DELIVERY_OPTIONS => 'integer', //tristate
        self::DROP_OFF_DELAY           => 'integer',
        self::EXPORT_AGE_CHECK         => 'integer', //tristate
        self::EXPORT_INSURANCE         => 'integer', //tristate
        self::EXPORT_LARGE_FORMAT      => 'integer', //tristate
        self::FIT_IN_MAILBOX           => 'integer',
        self::PACKAGE_TYPE             => 'string',
        self::RETURN_SHIPMENTS         => 'integer', //tristate
    ];
}
