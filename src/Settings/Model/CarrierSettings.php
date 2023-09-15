<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;

/**
 * @property string               $id
 * @property bool                 $allowDeliveryOptions
 * @property bool                 $allowEveningDelivery
 * @property bool                 $allowMondayDelivery
 * @property bool                 $allowMorningDelivery
 * @property bool                 $allowOnlyRecipient
 * @property bool                 $allowPickupLocations
 * @property bool                 $allowSameDayDelivery
 * @property bool                 $allowSaturdayDelivery
 * @property bool                 $allowSignature
 * @property string               $cutoffTime
 * @property string               $cutoffTimeSameDay
 * @property string               $defaultPackageType
 * @property int                  $deliveryDaysWindow
 * @property string               $deliveryOptionsCustomCss
 * @property bool                 $deliveryOptionsEnabled
 * @property bool                 $deliveryOptionsEnabledForBackorders
 * @property int                  $digitalStampDefaultWeight
 * @property int                  $dropOffDelay
 * @property DropOffPossibilities $dropOffPossibilities
 * @property bool                 $exportAgeCheck
 * @property bool                 $exportHideSender
 * @property bool                 $exportInsurance
 * @property int                  $exportInsuranceFromAmount
 * @property int                  $exportInsurancePricePercentage
 * @property int                  $exportInsuranceUpTo
 * @property int                  $exportInsuranceUpToEu
 * @property int                  $exportInsuranceUpToRow
 * @property int                  $exportInsuranceUpToUnique
 * @property bool                 $exportLargeFormat
 * @property bool                 $exportOnlyRecipient
 * @property bool                 $exportReturn
 * @property bool                 $exportReturnLargeFormat
 * @property string               $exportReturnPackageType
 * @property bool                 $exportSignature
 * @property float                $priceDeliveryTypeEvening
 * @property float                $priceDeliveryTypeMonday
 * @property float                $priceDeliveryTypeMorning
 * @property float                $priceDeliveryTypePickup
 * @property float                $priceDeliveryTypeSameDay
 * @property float                $priceDeliveryTypeSaturday
 * @property float                $priceDeliveryTypeStandard
 * @property float                $priceOnlyRecipient
 * @property float                $pricePackageTypeDigitalStamp
 * @property float                $pricePackageTypeMailbox
 * @property float                $priceSignature
 */
class CarrierSettings extends AbstractSettingsModel
{
    /**
     * Settings category ID.
     */
    final public const ID           = 'carrier';
    final public const CARRIER_NAME = 'carrierName';
    /**
     * Settings in this category.
     */
    final public const ALLOW_DELIVERY_OPTIONS                  = 'allowDeliveryOptions';
    final public const ALLOW_EVENING_DELIVERY                  = 'allowEveningDelivery';
    final public const ALLOW_MONDAY_DELIVERY                   = 'allowMondayDelivery';
    final public const ALLOW_MORNING_DELIVERY                  = 'allowMorningDelivery';
    final public const ALLOW_ONLY_RECIPIENT                    = 'allowOnlyRecipient';
    final public const ALLOW_PICKUP_LOCATIONS                  = 'allowPickupLocations';
    final public const ALLOW_SAME_DAY_DELIVERY                 = 'allowSameDayDelivery';
    final public const ALLOW_SATURDAY_DELIVERY                 = 'allowSaturdayDelivery';
    final public const ALLOW_SIGNATURE                         = 'allowSignature';
    final public const CUTOFF_TIME                             = 'cutoffTime';
    final public const CUTOFF_TIME_SAME_DAY                    = 'cutoffTimeSameDay';
    final public const DEFAULT_PACKAGE_TYPE                    = 'defaultPackageType';
    final public const DELIVERY_DAYS_WINDOW                    = 'deliveryDaysWindow';
    final public const DELIVERY_OPTIONS_CUSTOM_CSS             = 'deliveryOptionsCustomCss';
    final public const DELIVERY_OPTIONS_ENABLED                = 'deliveryOptionsEnabled';
    final public const DELIVERY_OPTIONS_ENABLED_FOR_BACKORDERS = 'deliveryOptionsEnabledForBackorders';
    final public const DIGITAL_STAMP_DEFAULT_WEIGHT            = 'digitalStampDefaultWeight';
    final public const DROP_OFF_DELAY                          = 'dropOffDelay';
    final public const DROP_OFF_POSSIBILITIES                  = 'dropOffPossibilities';
    final public const EXPORT_AGE_CHECK                        = 'exportAgeCheck';
    final public const EXPORT_HIDE_SENDER                      = 'exportHideSender';
    final public const EXPORT_INSURANCE                        = 'exportInsurance';
    final public const EXPORT_INSURANCE_FROM_AMOUNT            = 'exportInsuranceFromAmount';
    final public const EXPORT_INSURANCE_PRICE_PERCENTAGE       = 'exportInsurancePricePercentage';
    final public const EXPORT_INSURANCE_UP_TO                  = 'exportInsuranceUpTo';
    final public const EXPORT_INSURANCE_UP_TO_EU               = 'exportInsuranceUpToEu';
    final public const EXPORT_INSURANCE_UP_TO_ROW              = 'exportInsuranceUpToRow';
    final public const EXPORT_INSURANCE_UP_TO_UNIQUE           = 'exportInsuranceUpToUnique';
    final public const EXPORT_LARGE_FORMAT                     = 'exportLargeFormat';
    final public const EXPORT_ONLY_RECIPIENT                   = 'exportOnlyRecipient';
    final public const EXPORT_RETURN                           = 'exportReturn';
    final public const EXPORT_RETURN_LARGE_FORMAT              = 'exportReturnLargeFormat';
    final public const EXPORT_RETURN_PACKAGE_TYPE              = 'exportReturnPackageType';
    final public const EXPORT_SIGNATURE                        = 'exportSignature';
    final public const PRICE_DELIVERY_TYPE_EVENING             = 'priceDeliveryTypeEvening';
    final public const PRICE_DELIVERY_TYPE_MONDAY              = 'priceDeliveryTypeMonday';
    final public const PRICE_DELIVERY_TYPE_MORNING             = 'priceDeliveryTypeMorning';
    final public const PRICE_DELIVERY_TYPE_PICKUP              = 'priceDeliveryTypePickup';
    final public const PRICE_DELIVERY_TYPE_SAME_DAY            = 'priceDeliveryTypeSameDay';
    final public const PRICE_DELIVERY_TYPE_SATURDAY            = 'priceDeliveryTypeSaturday';
    final public const PRICE_DELIVERY_TYPE_STANDARD            = 'priceDeliveryTypeStandard';
    final public const PRICE_ONLY_RECIPIENT                    = 'priceOnlyRecipient';
    final public const PRICE_PACKAGE_TYPE_DIGITAL_STAMP        = 'pricePackageTypeDigitalStamp';
    final public const PRICE_PACKAGE_TYPE_MAILBOX              = 'pricePackageTypeMailbox';
    final public const PRICE_SIGNATURE                         = 'priceSignature';
    final public const SHOW_DELIVERY_DAY                       = 'showDeliveryDay';

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
        self::CUTOFF_TIME                             => '16:00',
        self::CUTOFF_TIME_SAME_DAY                    => '10:00',
        self::DEFAULT_PACKAGE_TYPE                    => DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME,
        self::DELIVERY_DAYS_WINDOW                    => 7,
        self::DELIVERY_OPTIONS_ENABLED                => false,
        self::DELIVERY_OPTIONS_ENABLED_FOR_BACKORDERS => false,
        self::DIGITAL_STAMP_DEFAULT_WEIGHT            => 0,
        self::DROP_OFF_DELAY                          => 0,
        self::DROP_OFF_POSSIBILITIES                  => DropOffPossibilities::class,
        self::EXPORT_AGE_CHECK                        => false,
        self::EXPORT_HIDE_SENDER                      => false,
        self::EXPORT_INSURANCE                        => false,
        self::EXPORT_INSURANCE_FROM_AMOUNT            => 0,
        self::EXPORT_INSURANCE_PRICE_PERCENTAGE       => 100,
        self::EXPORT_INSURANCE_UP_TO                  => 0,
        self::EXPORT_INSURANCE_UP_TO_EU               => 0,
        self::EXPORT_INSURANCE_UP_TO_ROW              => 0,
        self::EXPORT_INSURANCE_UP_TO_UNIQUE           => 0,
        self::EXPORT_LARGE_FORMAT                     => false,
        self::EXPORT_ONLY_RECIPIENT                   => false,
        self::EXPORT_RETURN                           => false,
        self::EXPORT_RETURN_LARGE_FORMAT              => false,
        self::EXPORT_RETURN_PACKAGE_TYPE              => DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME,
        self::EXPORT_SIGNATURE                        => false,
        self::PRICE_DELIVERY_TYPE_EVENING             => 0,
        self::PRICE_DELIVERY_TYPE_MONDAY              => 0,
        self::PRICE_DELIVERY_TYPE_MORNING             => 0,
        self::PRICE_DELIVERY_TYPE_PICKUP              => 0,
        self::PRICE_DELIVERY_TYPE_SAME_DAY            => 0,
        self::PRICE_DELIVERY_TYPE_SATURDAY            => 0,
        self::PRICE_DELIVERY_TYPE_STANDARD            => 0,
        self::PRICE_ONLY_RECIPIENT                    => 0,
        self::PRICE_PACKAGE_TYPE_DIGITAL_STAMP        => 0,
        self::PRICE_PACKAGE_TYPE_MAILBOX              => 0,
        self::PRICE_SIGNATURE                         => 0,
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
        self::DELIVERY_DAYS_WINDOW                    => 'int',
        self::DELIVERY_OPTIONS_CUSTOM_CSS             => 'string',
        self::DELIVERY_OPTIONS_ENABLED                => 'bool',
        self::DELIVERY_OPTIONS_ENABLED_FOR_BACKORDERS => 'bool',
        self::DIGITAL_STAMP_DEFAULT_WEIGHT            => 'int',
        self::DROP_OFF_DELAY                          => 'int',
        self::DROP_OFF_POSSIBILITIES                  => DropOffPossibilities::class,
        self::EXPORT_AGE_CHECK                        => 'bool',
        self::EXPORT_INSURANCE                        => 'bool',
        self::EXPORT_INSURANCE_FROM_AMOUNT            => 'int',
        self::EXPORT_INSURANCE_PRICE_PERCENTAGE       => 'float',
        self::EXPORT_INSURANCE_UP_TO                  => 'int',
        self::EXPORT_INSURANCE_UP_TO_EU               => 'int',
        self::EXPORT_INSURANCE_UP_TO_ROW              => 'int',
        self::EXPORT_INSURANCE_UP_TO_UNIQUE           => 'int',
        self::EXPORT_LARGE_FORMAT                     => 'bool',
        self::EXPORT_ONLY_RECIPIENT                   => 'bool',
        self::EXPORT_RETURN                           => 'bool',
        self::EXPORT_RETURN_LARGE_FORMAT              => 'bool',
        self::EXPORT_RETURN_PACKAGE_TYPE              => 'string',
        self::EXPORT_SIGNATURE                        => 'bool',
        self::PRICE_DELIVERY_TYPE_EVENING             => 'float',
        self::PRICE_DELIVERY_TYPE_MONDAY              => 'float',
        self::PRICE_DELIVERY_TYPE_MORNING             => 'float',
        self::PRICE_DELIVERY_TYPE_PICKUP              => 'float',
        self::PRICE_DELIVERY_TYPE_SAME_DAY            => 'float',
        self::PRICE_DELIVERY_TYPE_SATURDAY            => 'float',
        self::PRICE_DELIVERY_TYPE_STANDARD            => 'float',
        self::PRICE_ONLY_RECIPIENT                    => 'float',
        self::PRICE_PACKAGE_TYPE_DIGITAL_STAMP        => 'float',
        self::PRICE_PACKAGE_TYPE_MAILBOX              => 'float',
        self::PRICE_SIGNATURE                         => 'float',
        self::SHOW_DELIVERY_DAY                       => 'bool',
    ];

    /**
     * @param  string|\MyParcelNL\Pdk\Carrier\Model\Carrier $carrier
     */
    public static function fromCarrier($carrier): self
    {
        if ($carrier instanceof Carrier) {
            $carrier = $carrier->externalIdentifier;
        }

        /** @var null|\MyParcelNL\Pdk\Settings\Model\CarrierSettings $settings */
        $settings = Settings::all()->carrier->get($carrier);

        if (! $settings) {
            return new CarrierSettings();
        }

        return clone $settings;
    }
}
