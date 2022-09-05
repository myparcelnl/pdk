<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Base\Service\CountryService;
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
class ProductSettings extends Model
{
    public const  ALLOW_ONLY_RECIPIENT     = 'allowOnlyRecipient';
    public const  ALLOW_SIGNATURE          = 'allowSignature';
    public const  COUNTRY_OF_ORIGIN        = 'countryOfOrigin';
    public const  CUSTOMS_CODE             = 'customsCode';
    public const  DISABLE_DELIVERY_OPTIONS = 'disableDeliveryOptions';
    public const  DROP_OFF_DELAY           = 'dropOffDelay';
    public const  EXPORT_AGE_CHECK         = 'exportAgeCheck';
    public const  EXPORT_INSURANCE         = 'exportInsurance';
    public const  EXPORT_LARGE_FORMAT      = 'exportLargeFormat';
    public const  FIT_IN_MAILBOX           = 'fitInMailbox';
    public const  PACKAGE_TYPE             = 'packageType';
    public const  RETURN_SHIPMENTS         = 'returnShipments';

    protected $attributes = [
        self::ALLOW_ONLY_RECIPIENT     => false,
        self::ALLOW_SIGNATURE          => false,
        self::COUNTRY_OF_ORIGIN        => CountryService::CC_NL,
        self::CUSTOMS_CODE             => '0',
        self::DISABLE_DELIVERY_OPTIONS => false,
        self::DROP_OFF_DELAY           => 0,
        self::EXPORT_AGE_CHECK         => false,
        self::EXPORT_INSURANCE         => false,
        self::EXPORT_LARGE_FORMAT      => false,
        self::FIT_IN_MAILBOX           => 0,
        self::PACKAGE_TYPE             => DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME,
        self::RETURN_SHIPMENTS         => false,
    ];

    protected $casts      = [
        self::ALLOW_ONLY_RECIPIENT     => 'bool',
        self::ALLOW_SIGNATURE          => 'bool',
        self::COUNTRY_OF_ORIGIN        => 'string',
        self::CUSTOMS_CODE             => 'string',
        self::DISABLE_DELIVERY_OPTIONS => 'bool',
        self::DROP_OFF_DELAY           => 'integer',
        self::EXPORT_AGE_CHECK         => 'bool',
        self::EXPORT_INSURANCE         => 'bool',
        self::EXPORT_LARGE_FORMAT      => 'bool',
        self::FIT_IN_MAILBOX           => 'integer',
        self::PACKAGE_TYPE             => 'string',
        self::RETURN_SHIPMENTS         => 'bool',
    ];
}
