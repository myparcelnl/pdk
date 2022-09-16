<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property bool                 $allowDeliveryOptions
 * @property bool                 $allowEveningDelivery
 * @property bool                 $allowMondayDelivery
 * @property bool                 $allowMorningDelivery
 * @property bool                 $allowOnlyRecipient
 * @property bool                 $allowPickupLocations
 * @property bool                 $allowSameDayDelivery
 * @property bool                 $allowSaturdayDelivery
 * @property bool                 $allowSignature
 * @property string               $defaultPackageType
 * @property string               $digitalStampDefaultWeight
 * @property DropOffPossibilities $dropOffPossibilities
 * @property bool                 $exportAgeCheck
 * @property bool                 $exportExtraLargeFormat
 * @property bool                 $exportInsured
 * @property int                  $exportInsuredAmount
 * @property int                  $exportInsuredAmountMax
 * @property bool                 $exportInsuredForBe
 * @property bool                 $exportOnlyRecipient
 * @property bool                 $exportReturnShipments
 * @property bool                 $exportSignature
 * @property bool                 $featureShowDeliveryDate
 * @property int                  $priceEveningDelivery
 * @property int                  $priceMorningDelivery
 * @property int                  $priceOnlyRecipient
 * @property int                  $pricePackageTypeDigitalStamp
 * @property int                  $pricePackageTypeMailbox
 * @property int                  $pricePickup
 * @property int                  $priceSameDayDelivery
 * @property int                  $priceSignature
 * @property int                  $priceStandardDelivery
 */
class CarrierSettings extends Model
{
    /**
     * Settings category ID.
     */
    public const ID = 'carrier';
    /**
     * Settings in this category.
     */
    public const ALLOW_DELIVERY_OPTIONS           = 'allowDeliveryOptions';
    public const ALLOW_EVENING_DELIVERY           = 'allowEveningDelivery';
    public const ALLOW_MONDAY_DELIVERY            = 'allowMondayDelivery';
    public const ALLOW_MORNING_DELIVERY           = 'allowMorningDelivery';
    public const ALLOW_ONLY_RECIPIENT             = 'allowOnlyRecipient';
    public const ALLOW_PICKUP_LOCATIONS           = 'allowPickupLocations';
    public const ALLOW_SAME_DAY_DELIVERY          = 'allowSameDayDelivery';
    public const ALLOW_SATURDAY_DELIVERY          = 'allowSaturdayDelivery';
    public const ALLOW_SIGNATURE                  = 'allowSignature';
    public const CARRIER_NAME                     = 'carrier';
    public const DEFAULT_PACKAGE_TYPE             = 'defaultPackageType';
    public const DIGITAL_STAMP_DEFAULT_WEIGHT     = 'digitalStampDefaultWeight';
    public const DROP_OFF_POSSIBILITIES           = 'dropOffPossibilities';
    public const EXPORT_AGE_CHECK                 = 'exportAgeCheck';
    public const EXPORT_EXTRA_LARGE_FORMAT        = 'exportExtraLargeFormat';
    public const EXPORT_INSURED                   = 'exportInsured';
    public const EXPORT_INSURED_AMOUNT            = 'exportInsuredAmount';
    public const EXPORT_INSURED_AMOUNT_MAX        = 'exportInsuredAmountMax';
    public const EXPORT_INSURED_FOR_BE            = 'exportInsuredForBe';
    public const EXPORT_ONLY_RECIPIENT            = 'exportOnlyRecipient';
    public const EXPORT_RETURN_SHIPMENTS          = 'exportReturnShipments';
    public const EXPORT_SIGNATURE                 = 'exportSignature';
    public const FEATURE_SHOW_DELIVERY_DATE       = 'featureShowDeliveryDate';
    public const PRICE_EVENING_DELIVERY           = 'priceEveningDelivery';
    public const PRICE_MORNING_DELIVERY           = 'priceMorningDelivery';
    public const PRICE_ONLY_RECIPIENT             = 'priceOnlyRecipient';
    public const PRICE_PACKAGE_TYPE_DIGITAL_STAMP = 'pricePackageTypeDigitalStamp';
    public const PRICE_PACKAGE_TYPE_MAILBOX       = 'pricePackageTypeMailbox';
    public const PRICE_PICKUP                     = 'pricePickup';
    public const PRICE_SAME_DAY_DELIVERY          = 'priceSameDayDelivery';
    public const PRICE_SIGNATURE                  = 'priceSignature';
    public const PRICE_STANDARD_DELIVERY          = 'priceStandardDelivery';

    protected $attributes = [
        self::CARRIER_NAME                     => null,
        self::ALLOW_DELIVERY_OPTIONS           => false,
        self::ALLOW_EVENING_DELIVERY           => false,
        self::ALLOW_MONDAY_DELIVERY            => false,
        self::ALLOW_MORNING_DELIVERY           => false,
        self::ALLOW_ONLY_RECIPIENT             => false,
        self::ALLOW_PICKUP_LOCATIONS           => false,
        self::ALLOW_SAME_DAY_DELIVERY          => false,
        self::ALLOW_SATURDAY_DELIVERY          => false,
        self::ALLOW_SIGNATURE                  => false,
        self::DEFAULT_PACKAGE_TYPE             => null,
        self::DIGITAL_STAMP_DEFAULT_WEIGHT     => null,
        self::DROP_OFF_POSSIBILITIES           => null,
        self::EXPORT_AGE_CHECK                 => false,
        self::EXPORT_EXTRA_LARGE_FORMAT        => false,
        self::EXPORT_INSURED                   => false,
        self::EXPORT_INSURED_AMOUNT            => null,
        self::EXPORT_INSURED_AMOUNT_MAX        => null,
        self::EXPORT_INSURED_FOR_BE            => false,
        self::EXPORT_ONLY_RECIPIENT            => false,
        self::EXPORT_RETURN_SHIPMENTS          => false,
        self::EXPORT_SIGNATURE                 => false,
        self::FEATURE_SHOW_DELIVERY_DATE       => null,
        self::PRICE_EVENING_DELIVERY           => null,
        self::PRICE_MORNING_DELIVERY           => null,
        self::PRICE_ONLY_RECIPIENT             => null,
        self::PRICE_PACKAGE_TYPE_DIGITAL_STAMP => null,
        self::PRICE_PACKAGE_TYPE_MAILBOX       => null,
        self::PRICE_PICKUP                     => null,
        self::PRICE_SAME_DAY_DELIVERY          => null,
        self::PRICE_SIGNATURE                  => null,
        self::PRICE_STANDARD_DELIVERY          => null,
    ];

    protected $casts      = [
        self::CARRIER_NAME                     => 'string',
        self::ALLOW_DELIVERY_OPTIONS           => 'bool',
        self::ALLOW_EVENING_DELIVERY           => 'bool',
        self::ALLOW_MONDAY_DELIVERY            => 'bool',
        self::ALLOW_MORNING_DELIVERY           => 'bool',
        self::ALLOW_ONLY_RECIPIENT             => 'bool',
        self::ALLOW_PICKUP_LOCATIONS           => 'bool',
        self::ALLOW_SAME_DAY_DELIVERY          => 'bool',
        self::ALLOW_SATURDAY_DELIVERY          => 'bool',
        self::ALLOW_SIGNATURE                  => 'bool',
        self::DEFAULT_PACKAGE_TYPE             => 'string',
        self::DIGITAL_STAMP_DEFAULT_WEIGHT     => 'string',
        self::DROP_OFF_POSSIBILITIES           => DropOffPossibilities::class,
        self::EXPORT_AGE_CHECK                 => 'bool',
        self::EXPORT_EXTRA_LARGE_FORMAT        => 'bool',
        self::EXPORT_INSURED                   => 'bool',
        self::EXPORT_INSURED_AMOUNT            => 'integer',
        self::EXPORT_INSURED_AMOUNT_MAX        => 'integer',
        self::EXPORT_INSURED_FOR_BE            => 'bool',
        self::EXPORT_ONLY_RECIPIENT            => 'bool',
        self::EXPORT_RETURN_SHIPMENTS          => 'bool',
        self::EXPORT_SIGNATURE                 => 'bool',
        self::FEATURE_SHOW_DELIVERY_DATE       => 'string',
        self::PRICE_EVENING_DELIVERY           => 'integer',
        self::PRICE_MORNING_DELIVERY           => 'integer',
        self::PRICE_ONLY_RECIPIENT             => 'integer',
        self::PRICE_PACKAGE_TYPE_DIGITAL_STAMP => 'integer',
        self::PRICE_PACKAGE_TYPE_MAILBOX       => 'integer',
        self::PRICE_PICKUP                     => 'integer',
        self::PRICE_SAME_DAY_DELIVERY          => 'integer',
        self::PRICE_SIGNATURE                  => 'integer',
        self::PRICE_STANDARD_DELIVERY          => 'integer',
    ];
}
