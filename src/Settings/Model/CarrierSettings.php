<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;

/**
 * @property string                                              $carrierName
 * @property bool                                                $allowDeliveryOptions
 * @property bool                                                $allowEveningDelivery
 * @property bool                                                $allowInsuranceBelgium
 * @property bool                                                $allowMondayDelivery
 * @property bool                                                $allowMorningDelivery
 * @property bool                                                $allowOnlyRecipient
 * @property bool                                                $allowPickupLocations
 * @property bool                                                $allowSameDayDelivery
 * @property bool                                                $allowSaturdayDelivery
 * @property bool                                                $allowSignature
 * @property string                                              $cutoffTime
 * @property string                                              $cutoffTimeSameDay
 * @property string                                              $defaultPackageType
 * @property string                                              $deliveryOptionsCustomCss
 * @property string                                              $deliveryOptionsDisplay
 * @property bool                                                $deliveryOptionsEnabledForBackorders
 * @property string                                              $deliveryOptionsPosition
 * @property int                                                 $digitalStampDefaultWeight
 * @property \MyParcelNL\Pdk\Settings\Model\DropOffPossibilities $dropOffPossibilities
 * @property int                                                 $dropOffDelay
 * @property string                                              $dropOffPoint
 * @property bool                                                $exportAgeCheck
 * @property bool                                                $exportInsurance
 * @property int                                                 $exportInsuranceAmount
 * @property int                                                 $exportInsuranceUpTo
 * @property bool                                                $exportLargeFormat
 * @property bool                                                $exportOnlyRecipient
 * @property bool                                                $exportReturnLargeFormat
 * @property string                                              $exportReturnPackageType
 * @property bool                                                $exportReturnShipments
 * @property bool                                                $exportSignature
 * @property bool                                                $featureShowDeliveryDate
 * @property string                                              $pickupLocationsDefaultView
 * @property int                                                 $priceEveningDelivery
 * @property int                                                 $priceMondayDelivery
 * @property int                                                 $priceMorningDelivery
 * @property int                                                 $priceOnlyRecipient
 * @property int                                                 $pricePackageTypeDigitalStamp
 * @property int                                                 $pricePackageTypeMailbox
 * @property int                                                 $pricePickup
 * @property int                                                 $priceSameDayDelivery
 * @property int                                                 $priceSignature
 * @property int                                                 $priceStandardDelivery
 * @property bool                                                $showDeliveryDay
 * @property bool                                                $showPriceAsSurcharge
 * @property bool                                                $useSeparateAddressFields
 * @property string                                              $stringAddressNotFound
 * @property string                                              $stringCity
 * @property string                                              $stringCountry
 * @property string                                              $stringDelivery
 * @property string                                              $stringDiscount
 * @property string                                              $stringEveningDelivery
 * @property string                                              $stringFrom
 * @property string                                              $stringHouseNumber
 * @property string                                              $stringLoadMore
 * @property string                                              $stringMorningDelivery
 * @property string                                              $stringOnlyRecipient
 * @property string                                              $stringOpeningHours
 * @property string                                              $stringPickup
 * @property string                                              $stringPickupLocationsListButton
 * @property string                                              $stringPickupLocationsMapButton
 * @property string                                              $stringPostalCode
 * @property string                                              $stringRecipient
 * @property string                                              $stringRetry
 * @property string                                              $stringSaturdayDelivery
 * @property string                                              $stringSignature
 * @property string                                              $stringStandardDelivery
 * @property string                                              $stringWrongNumberPostalCode
 * @property string                                              $stringWrongPostalCodeCity
 */
class CarrierSettings extends Model
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
    public const ALLOW_INSURANCE_BELGIUM                 = 'allowInsuranceBelgium';
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
    public const DELIVERY_OPTIONS_POSITION               = 'deliveryOptionsPosition';
    public const DIGITAL_STAMP_DEFAULT_WEIGHT            = 'digitalStampDefaultWeight';
    public const DROP_OFF_DELAY                          = 'dropOffDelay';
    public const DROP_OFF_POINT                          = 'dropOffPoint';
    public const DROP_OFF_POSSIBILITIES                  = 'dropOffPossibilities';
    public const EXPORT_AGE_CHECK                        = 'exportAgeCheck';
    public const EXPORT_INSURANCE                        = 'exportInsurance';
    public const EXPORT_INSURANCE_AMOUNT                 = 'exportInsuranceAmount';
    public const EXPORT_INSURANCE_UP_TO                  = 'exportInsuranceUpTo';
    public const EXPORT_LARGE_FORMAT                     = 'exportLargeFormat';
    public const EXPORT_ONLY_RECIPIENT                   = 'exportOnlyRecipient';
    public const EXPORT_RETURN_LARGE_FORMAT              = 'exportReturnLargeFormat';
    public const EXPORT_RETURN_PACKAGE_TYPE              = 'exportReturnPackageType';
    public const EXPORT_RETURN_SHIPMENTS                 = 'exportReturnShipments';
    public const EXPORT_SIGNATURE                        = 'exportSignature';
    public const FEATURE_SHOW_DELIVERY_DATE              = 'featureShowDeliveryDate';
    public const PICKUP_LOCATIONS_DEFAULT_VIEW           = 'pickupLocationsDefaultView';
    public const PRICE_EVENING_DELIVERY                  = 'priceEveningDelivery';
    public const PRICE_MONDAY_DELIVERY                   = 'priceMondayDelivery';
    public const PRICE_MORNING_DELIVERY                  = 'priceMorningDelivery';
    public const PRICE_ONLY_RECIPIENT                    = 'priceOnlyRecipient';
    public const PRICE_PACKAGE_TYPE_DIGITAL_STAMP        = 'pricePackageTypeDigitalStamp';
    public const PRICE_PACKAGE_TYPE_MAILBOX              = 'pricePackageTypeMailbox';
    public const PRICE_PICKUP                            = 'pricePickup';
    public const PRICE_SAME_DAY_DELIVERY                 = 'priceSameDayDelivery';
    public const PRICE_SIGNATURE                         = 'priceSignature';
    public const PRICE_STANDARD_DELIVERY                 = 'priceStandardDelivery';
    public const SHOW_DELIVERY_DAY                       = 'showDeliveryDay';
    public const SHOW_PRICE_AS_SURCHARGE                 = 'showPriceAsSurcharge';
    public const STRING_ADDRESS_NOT_FOUND                = 'stringAddressNotFound';
    public const STRING_CITY                             = 'stringCity';
    public const STRING_COUNTRY                          = 'stringCountry';
    public const STRING_DELIVERY                         = 'stringDelivery';
    public const STRING_DISCOUNT                         = 'stringDiscount';
    public const STRING_EVENING_DELIVERY                 = 'stringEveningDelivery';
    public const STRING_FROM                             = 'stringFrom';
    public const STRING_HOUSE_NUMBER                     = 'stringHouseNumber';
    public const STRING_LOAD_MORE                        = 'stringLoadMore';
    public const STRING_MORNING_DELIVERY                 = 'stringMorningDelivery';
    public const STRING_ONLY_RECIPIENT                   = 'stringOnlyRecipient';
    public const STRING_OPENING_HOURS                    = 'stringOpeningHours';
    public const STRING_PICKUP                           = 'stringPickup';
    public const STRING_PICKUP_LOCATIONS_LIST_BUTTON     = 'stringPickupLocationsListButton';
    public const STRING_PICKUP_LOCATIONS_MAP_BUTTON      = 'stringPickupLocationsMapButton';
    public const STRING_POSTAL_CODE                      = 'stringPostalCode';
    public const STRING_RECIPIENT                        = 'stringRecipient';
    public const STRING_RETRY                            = 'stringRetry';
    public const STRING_SATURDAY_DELIVERY                = 'stringSaturdayDelivery';
    public const STRING_SIGNATURE                        = 'stringSignature';
    public const STRING_STANDARD_DELIVERY                = 'stringStandardDelivery';
    public const STRING_WRONG_NUMBER_POSTAL_CODE         = 'stringWrongNumberPostalCode';
    public const STRING_WRONG_POSTAL_CODE_CITY           = 'stringWrongPostalCodeCity';
    public const USE_SEPARATE_ADDRESS_FIELDS             = 'useSeparateAddressFields';

    protected $attributes = [
        self::CARRIER_NAME => null,

        self::ALLOW_DELIVERY_OPTIONS                  => false,
        self::ALLOW_EVENING_DELIVERY                  => false,
        self::ALLOW_INSURANCE_BELGIUM                 => false,
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
        self::EXPORT_INSURANCE_AMOUNT                 => 0,
        self::EXPORT_INSURANCE_UP_TO                  => 0,
        self::EXPORT_LARGE_FORMAT                     => false,
        self::EXPORT_ONLY_RECIPIENT                   => false,
        self::EXPORT_RETURN_LARGE_FORMAT              => false,
        self::EXPORT_RETURN_PACKAGE_TYPE              => DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME,
        self::EXPORT_RETURN_SHIPMENTS                 => false,
        self::EXPORT_SIGNATURE                        => false,
        self::FEATURE_SHOW_DELIVERY_DATE              => true,
        self::PRICE_EVENING_DELIVERY                  => 0,
        self::PRICE_MONDAY_DELIVERY                   => 0,
        self::PRICE_MORNING_DELIVERY                  => 0,
        self::PRICE_ONLY_RECIPIENT                    => 0,
        self::PRICE_PACKAGE_TYPE_DIGITAL_STAMP        => 0,
        self::PRICE_PACKAGE_TYPE_MAILBOX              => 0,
        self::PRICE_PICKUP                            => 0,
        self::PRICE_SAME_DAY_DELIVERY                 => 0,
        self::PRICE_SIGNATURE                         => 0,
        self::PRICE_STANDARD_DELIVERY                 => 0,
        self::SHOW_DELIVERY_DAY                       => true,

        self::STRING_ADDRESS_NOT_FOUND            => null,
        self::STRING_CITY                         => null,
        self::STRING_COUNTRY                      => null,
        self::STRING_DELIVERY                     => null,
        self::STRING_DISCOUNT                     => null,
        self::STRING_EVENING_DELIVERY             => null,
        self::STRING_FROM                         => null,
        self::STRING_HOUSE_NUMBER                 => null,
        self::STRING_LOAD_MORE                    => null,
        self::STRING_MORNING_DELIVERY             => null,
        self::STRING_ONLY_RECIPIENT               => null,
        self::STRING_OPENING_HOURS                => null,
        self::STRING_PICKUP                       => null,
        self::STRING_PICKUP_LOCATIONS_LIST_BUTTON => null,
        self::STRING_PICKUP_LOCATIONS_MAP_BUTTON  => null,
        self::STRING_POSTAL_CODE                  => null,
        self::STRING_RECIPIENT                    => null,
        self::STRING_RETRY                        => null,
        self::STRING_SATURDAY_DELIVERY            => null,
        self::STRING_SIGNATURE                    => null,
        self::STRING_STANDARD_DELIVERY            => null,
        self::STRING_WRONG_NUMBER_POSTAL_CODE     => null,
        self::STRING_WRONG_POSTAL_CODE_CITY       => null,
    ];

    protected $casts      = [
        self::CARRIER_NAME => 'string',

        self::ALLOW_DELIVERY_OPTIONS                  => 'bool',
        self::ALLOW_EVENING_DELIVERY                  => 'bool',
        self::ALLOW_INSURANCE_BELGIUM                 => 'bool',
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
        self::DELIVERY_OPTIONS_POSITION               => 'string',
        self::DIGITAL_STAMP_DEFAULT_WEIGHT            => 'int',
        self::DROP_OFF_POSSIBILITIES                  => DropOffPossibilities::class,
        self::DROP_OFF_DELAY                          => 'int',
        self::DROP_OFF_POINT                          => 'string',
        self::EXPORT_AGE_CHECK                        => 'bool',
        self::EXPORT_INSURANCE                        => 'bool',
        self::EXPORT_INSURANCE_AMOUNT                 => 'int',
        self::EXPORT_INSURANCE_UP_TO                  => 'int',
        self::EXPORT_LARGE_FORMAT                     => 'bool',
        self::EXPORT_ONLY_RECIPIENT                   => 'bool',
        self::EXPORT_RETURN_LARGE_FORMAT              => 'bool',
        self::EXPORT_RETURN_PACKAGE_TYPE              => 'string',
        self::EXPORT_RETURN_SHIPMENTS                 => 'bool',
        self::EXPORT_SIGNATURE                        => 'bool',
        self::FEATURE_SHOW_DELIVERY_DATE              => 'bool',
        self::PICKUP_LOCATIONS_DEFAULT_VIEW           => 'string',
        self::PRICE_EVENING_DELIVERY                  => 'int',
        self::PRICE_MONDAY_DELIVERY                   => 'int',
        self::PRICE_MORNING_DELIVERY                  => 'int',
        self::PRICE_ONLY_RECIPIENT                    => 'int',
        self::PRICE_PACKAGE_TYPE_DIGITAL_STAMP        => 'int',
        self::PRICE_PACKAGE_TYPE_MAILBOX              => 'int',
        self::PRICE_PICKUP                            => 'int',
        self::PRICE_SAME_DAY_DELIVERY                 => 'int',
        self::PRICE_SIGNATURE                         => 'int',
        self::PRICE_STANDARD_DELIVERY                 => 'int',
        self::SHOW_DELIVERY_DAY                       => 'bool',
        self::SHOW_PRICE_AS_SURCHARGE                 => 'bool',
        self::USE_SEPARATE_ADDRESS_FIELDS             => 'bool',

        self::STRING_ADDRESS_NOT_FOUND            => 'string',
        self::STRING_CITY                         => 'string',
        self::STRING_COUNTRY                      => 'string',
        self::STRING_DELIVERY                     => 'string',
        self::STRING_DISCOUNT                     => 'string',
        self::STRING_EVENING_DELIVERY             => 'string',
        self::STRING_FROM                         => 'string',
        self::STRING_HOUSE_NUMBER                 => 'string',
        self::STRING_LOAD_MORE                    => 'string',
        self::STRING_MORNING_DELIVERY             => 'string',
        self::STRING_ONLY_RECIPIENT               => 'string',
        self::STRING_OPENING_HOURS                => 'string',
        self::STRING_PICKUP                       => 'string',
        self::STRING_PICKUP_LOCATIONS_LIST_BUTTON => 'string',
        self::STRING_PICKUP_LOCATIONS_MAP_BUTTON  => 'string',
        self::STRING_POSTAL_CODE                  => 'string',
        self::STRING_RECIPIENT                    => 'string',
        self::STRING_RETRY                        => 'string',
        self::STRING_SATURDAY_DELIVERY            => 'string',
        self::STRING_SIGNATURE                    => 'string',
        self::STRING_STANDARD_DELIVERY            => 'string',
        self::STRING_WRONG_NUMBER_POSTAL_CODE     => 'string',
        self::STRING_WRONG_POSTAL_CODE_CITY       => 'string',
    ];
}
