<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;

/**
 * @property string                              $id
 * @property string                              $carrierName
 * @property bool                                $allowDeliveryOptions
 * @property bool                                $allowEveningDelivery
 * @property bool                                $allowInsuranceBelgium
 * @property bool                                $allowMondayDelivery
 * @property bool                                $allowMorningDelivery
 * @property bool                                $allowOnlyRecipient
 * @property bool                                $allowPickupLocations
 * @property bool                                $allowSameDayDelivery
 * @property bool                                $allowSaturdayDelivery
 * @property bool                                $allowSignature
 * @property string                              $cutoffTime
 * @property string                              $cutoffTimeSameDay
 * @property string                              $defaultPackageType
 * @property string                              $deliveryOptionsCustomCss
 * @property string                              $deliveryOptionsDisplay
 * @property bool                                $deliveryOptionsEnabledForBackorders
 * @property int                                 $digitalStampDefaultWeight
 * @property int                                 $dropOffDelay
 * @property string                              $dropOffPoint
 * @property DropOffPossibilities                $dropOffPossibilities
 * @property bool                                $exportAgeCheck
 * @property bool                                $exportInsurance
 * @property int                                 $exportInsuranceAmount
 * @property int                                 $exportInsuranceUpTo
 * @property bool                                $exportLargeFormat
 * @property bool                                $exportOnlyRecipient
 * @property bool                                $exportReturnLargeFormat
 * @property string                              $exportReturnPackageType
 * @property bool                                $exportReturnShipments
 * @property bool                                $exportSignature
 * @property int                                 $priceDeliveryTypeEvening
 * @property int                                 $priceDeliveryTypeMonday
 * @property int                                 $priceDeliveryTypeMorning
 * @property int                                 $priceDeliveryTypePickup
 * @property int                                 $priceDeliveryTypeSameDay
 * @property int                                 $priceDeliveryTypeStandard
 * @property int                                 $priceOnlyRecipient
 * @property int                                 $pricePackageTypeDigitalStamp
 * @property int                                 $pricePackageTypeMailbox
 * @property int                                 $priceSignature
 * @property ShippingMethodPackageTypeCollection $shippingMethodPackageTypes
 * @property bool                                $showDeliveryDay
 */
class CarrierSettings extends AbstractSettingsModel
{
    /**
     * Settings category ID.
     */
    public const ID           = 'carrier';
    public const CARRIER_NAME = 'carrierName';
    /**
     * Settings in this category.
     */
    public const ALLOW_DELIVERY_OPTIONS                  = 'allowDeliveryOptions';
    public const ALLOW_EVENING_DELIVERY                  = 'allowEveningDelivery';
    public const ALLOW_MONDAY_DELIVERY                   = 'allowMondayDelivery';
    public const ALLOW_MORNING_DELIVERY                  = 'allowMorningDelivery';
    public const ALLOW_ONLY_RECIPIENT                    = 'allowOnlyRecipient';
    public const ALLOW_PICKUP_LOCATIONS                  = 'allowPickupLocations';
    public const ALLOW_SAME_DAY_DELIVERY                 = 'allowSameDayDelivery';
    public const ALLOW_SATURDAY_DELIVERY                 = 'allowSaturdayDelivery';
    public const ALLOW_SIGNATURE                         = 'allowSignature';
    public const CUTOFF_TIME                             = 'cutoffTime';
    public const CUTOFF_TIME_SAME_DAY                    = 'cutoffTimeSameDay';
    public const DEFAULT_PACKAGE_TYPE                    = 'defaultPackageType';
    public const DELIVERY_OPTIONS_CUSTOM_CSS             = 'deliveryOptionsCustomCss';
    public const DELIVERY_OPTIONS_DISPLAY                = 'deliveryOptionsDisplay';
    public const DELIVERY_OPTIONS_ENABLED_FOR_BACKORDERS = 'deliveryOptionsEnabledForBackorders';
    public const DIGITAL_STAMP_DEFAULT_WEIGHT            = 'digitalStampDefaultWeight';
    public const DROP_OFF_DELAY                          = 'dropOffDelay';
    public const DROP_OFF_POINT                          = 'dropOffPoint';
    public const DROP_OFF_POSSIBILITIES                  = 'dropOffPossibilities';
    public const EXPORT_AGE_CHECK                        = 'exportAgeCheck';
    public const EXPORT_INSURANCE                        = 'exportInsurance';
    public const EXPORT_INSURANCE_FROM_AMOUNT            = 'exportInsuranceFromAmount';
    public const EXPORT_INSURANCE_UP_TO                  = 'exportInsuranceUpTo';
    public const EXPORT_INSURANCE_UP_TO_BE               = 'exportInsuranceUpToBe';
    public const EXPORT_INSURANCE_UP_TO_EU               = 'exportInsuranceUpToEu';
    public const EXPORT_LARGE_FORMAT                     = 'exportLargeFormat';
    public const EXPORT_ONLY_RECIPIENT                   = 'exportOnlyRecipient';
    public const EXPORT_RETURN_LARGE_FORMAT              = 'exportReturnLargeFormat';
    public const EXPORT_RETURN_PACKAGE_TYPE              = 'exportReturnPackageType';
    public const EXPORT_RETURN_SHIPMENTS                 = 'exportReturnShipments';
    public const EXPORT_SIGNATURE                        = 'exportSignature';
    public const PRICE_DELIVERY_TYPE_EVENING             = 'priceDeliveryTypeEvening';
    public const PRICE_DELIVERY_TYPE_MONDAY              = 'priceDeliveryTypeMonday';
    public const PRICE_DELIVERY_TYPE_MORNING             = 'priceDeliveryTypeMorning';
    public const PRICE_DELIVERY_TYPE_PICKUP              = 'priceDeliveryTypePickup';
    public const PRICE_DELIVERY_TYPE_SAME_DAY            = 'priceDeliveryTypeSameDay';
    public const PRICE_DELIVERY_TYPE_STANDARD            = 'priceDeliveryTypeStandard';
    public const PRICE_ONLY_RECIPIENT                    = 'priceOnlyRecipient';
    public const PRICE_PACKAGE_TYPE_DIGITAL_STAMP        = 'pricePackageTypeDigitalStamp';
    public const PRICE_PACKAGE_TYPE_MAILBOX              = 'pricePackageTypeMailbox';
    public const PRICE_SIGNATURE                         = 'priceSignature';
    public const SHIPPING_METHOD_PACKAGE_TYPES           = 'shippingMethodPackageTypes';
    public const SHOW_DELIVERY_DAY                       = 'showDeliveryDay';

    protected $attributes = [
        'id'               => self::ID,
        self::CARRIER_NAME => null,

        self::ALLOW_DELIVERY_OPTIONS                  => false,
        self::ALLOW_EVENING_DELIVERY                  => false,
        self::ALLOW_MONDAY_DELIVERY                   => false,
        self::ALLOW_MORNING_DELIVERY                  => false,
        self::ALLOW_ONLY_RECIPIENT                    => false,
        self::ALLOW_PICKUP_LOCATIONS                  => false,
        self::ALLOW_SAME_DAY_DELIVERY                 => false,
        self::ALLOW_SATURDAY_DELIVERY                 => false,
        self::ALLOW_SIGNATURE                         => false,
        self::CUTOFF_TIME                             => null,
        self::CUTOFF_TIME_SAME_DAY                    => null,
        self::DEFAULT_PACKAGE_TYPE                    => DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME,
        self::DELIVERY_OPTIONS_DISPLAY                => null,
        self::DELIVERY_OPTIONS_ENABLED_FOR_BACKORDERS => false,
        self::DIGITAL_STAMP_DEFAULT_WEIGHT            => 0,
        self::DROP_OFF_DELAY                          => 0,
        self::DROP_OFF_POINT                          => null,
        self::DROP_OFF_POSSIBILITIES                  => DropOffPossibilities::class,
        self::EXPORT_AGE_CHECK                        => false,
        self::EXPORT_INSURANCE                        => false,
        self::EXPORT_INSURANCE_FROM_AMOUNT            => 0,
        self::EXPORT_INSURANCE_UP_TO                  => 0,
        self::EXPORT_INSURANCE_UP_TO_BE               => 0,
        self::EXPORT_INSURANCE_UP_TO_EU               => 0,
        self::EXPORT_LARGE_FORMAT                     => false,
        self::EXPORT_ONLY_RECIPIENT                   => false,
        self::EXPORT_RETURN_LARGE_FORMAT              => false,
        self::EXPORT_RETURN_PACKAGE_TYPE              => DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME,
        self::EXPORT_RETURN_SHIPMENTS                 => false,
        self::EXPORT_SIGNATURE                        => false,
        self::PRICE_DELIVERY_TYPE_EVENING             => 0,
        self::PRICE_DELIVERY_TYPE_MONDAY              => 0,
        self::PRICE_DELIVERY_TYPE_MORNING             => 0,
        self::PRICE_DELIVERY_TYPE_SAME_DAY            => 0,
        self::PRICE_DELIVERY_TYPE_STANDARD            => 0,
        self::PRICE_ONLY_RECIPIENT                    => 0,
        self::PRICE_PACKAGE_TYPE_DIGITAL_STAMP        => 0,
        self::PRICE_PACKAGE_TYPE_MAILBOX              => 0,
        self::PRICE_DELIVERY_TYPE_PICKUP              => 0,
        self::PRICE_SIGNATURE                         => 0,
        self::SHIPPING_METHOD_PACKAGE_TYPES           => ShippingMethodPackageTypeCollection::class,
        self::SHOW_DELIVERY_DAY                       => true,
    ];

    protected $casts      = [
        self::CARRIER_NAME => 'string',

        self::ALLOW_DELIVERY_OPTIONS                  => 'bool',
        self::ALLOW_EVENING_DELIVERY                  => 'bool',
        self::ALLOW_MONDAY_DELIVERY                   => 'bool',
        self::ALLOW_MORNING_DELIVERY                  => 'bool',
        self::ALLOW_ONLY_RECIPIENT                    => 'bool',
        self::ALLOW_PICKUP_LOCATIONS                  => 'bool',
        self::ALLOW_SAME_DAY_DELIVERY                 => 'bool',
        self::ALLOW_SATURDAY_DELIVERY                 => 'bool',
        self::ALLOW_SIGNATURE                         => 'bool',
        self::CUTOFF_TIME                             => 'string',
        self::CUTOFF_TIME_SAME_DAY                    => 'string',
        self::DEFAULT_PACKAGE_TYPE                    => 'string',
        self::DELIVERY_OPTIONS_CUSTOM_CSS             => 'string',
        self::DELIVERY_OPTIONS_DISPLAY                => 'string',
        self::DELIVERY_OPTIONS_ENABLED_FOR_BACKORDERS => 'bool',
        self::DIGITAL_STAMP_DEFAULT_WEIGHT            => 'int',
        self::DROP_OFF_DELAY                          => 'int',
        self::DROP_OFF_POINT                          => 'string',
        self::DROP_OFF_POSSIBILITIES                  => DropOffPossibilities::class,
        self::EXPORT_AGE_CHECK                        => 'bool',
        self::EXPORT_INSURANCE                        => 'bool',
        self::EXPORT_INSURANCE_FROM_AMOUNT            => 'int',
        self::EXPORT_INSURANCE_UP_TO                  => 'int',
        self::EXPORT_INSURANCE_UP_TO_BE               => 'int',
        self::EXPORT_INSURANCE_UP_TO_EU               => 'int',
        self::EXPORT_LARGE_FORMAT                     => 'bool',
        self::EXPORT_ONLY_RECIPIENT                   => 'bool',
        self::EXPORT_RETURN_LARGE_FORMAT              => 'bool',
        self::EXPORT_RETURN_PACKAGE_TYPE              => 'string',
        self::EXPORT_RETURN_SHIPMENTS                 => 'bool',
        self::EXPORT_SIGNATURE                        => 'bool',
        self::PRICE_DELIVERY_TYPE_EVENING             => 'int',
        self::PRICE_DELIVERY_TYPE_MONDAY              => 'int',
        self::PRICE_DELIVERY_TYPE_MORNING             => 'int',
        self::PRICE_DELIVERY_TYPE_SAME_DAY            => 'int',
        self::PRICE_DELIVERY_TYPE_STANDARD            => 'int',
        self::PRICE_ONLY_RECIPIENT                    => 'int',
        self::PRICE_PACKAGE_TYPE_DIGITAL_STAMP        => 'int',
        self::PRICE_PACKAGE_TYPE_MAILBOX              => 'int',
        self::PRICE_DELIVERY_TYPE_PICKUP              => 'int',
        self::PRICE_SIGNATURE                         => 'int',
        self::SHIPPING_METHOD_PACKAGE_TYPES           => ShippingMethodPackageTypeCollection::class,
        self::SHOW_DELIVERY_DAY                       => 'bool',
    ];
}
