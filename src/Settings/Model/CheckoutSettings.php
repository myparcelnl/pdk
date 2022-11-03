<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property string $deliveryOptionsCustomCss
 * @property bool   $deliveryOptionsDisplay
 * @property string $deliveryOptionsPosition
 * @property string $pickupLocationsDefaultView
 * @property string $priceType
 * @property bool   $showDeliveryDay
 * @property bool   $showPriceAsSurcharge
 * @property bool   $useSeparateAddressFields
 * @property string $stringAddressNotFound
 * @property string $stringCountry
 * @property string $stringCity
 * @property string $stringDelivery
 * @property string $stringDiscount
 * @property string $stringEveningDelivery
 * @property string $stringFrom
 * @property string $stringHouseNumber
 * @property string $stringLoadMore
 * @property string $stringMorningDelivery
 * @property string $stringOnlyRecipient
 * @property string $stringOpeningHours
 * @property string $stringPickupLocationsListButton
 * @property string $stringPickupLocationsMapButton
 * @property string $stringPickup
 * @property string $stringPostalCode
 * @property string $stringRecipient
 * @property string $stringRetry
 * @property string $stringSaturdayDelivery
 * @property string $stringSignature
 * @property string $stringStandardDelivery
 * @property string $stringWrongNumberPostalCode
 * @property string $stringWrongPostalCodeCity
 */
class CheckoutSettings extends Model
{
    /**
     * Settings category ID.
     */
    public const ID = 'checkout';
    /**
     * Settings in this category.
     */
    public const DELIVERY_OPTIONS_CUSTOM_CSS         = 'deliveryOptionsCustomCss';
    public const DELIVERY_OPTIONS_DISPLAY            = 'deliveryOptionsDisplay';
    public const DELIVERY_OPTIONS_POSITION           = 'deliveryOptionsPosition';
    public const PICKUP_LOCATIONS_DEFAULT_VIEW       = 'pickupLocationsDefaultView';
    public const PRICE_TYPE                          = 'priceType';
    public const SHOW_DELIVERY_DAY                   = 'showDeliveryDay';
    public const SHOW_PRICE_AS_SURCHARGE             = 'showPriceAsSurcharge';
    public const USE_SEPARATE_ADDRESS_FIELDS         = 'useSeparateAddressFields';
    public const STRING_ADDRESS_NOT_FOUND            = 'stringAddressNotFound';
    public const STRING_CITY                         = 'stringCity';
    public const STRING_COUNTRY                      = 'stringCountry';
    public const STRING_DELIVERY                     = 'stringDelivery';
    public const STRING_DISCOUNT                     = 'stringDiscount';
    public const STRING_EVENING_DELIVERY             = 'stringEveningDelivery';
    public const STRING_FROM                         = 'stringFrom';
    public const STRING_HOUSE_NUMBER                 = 'stringHouseNumber';
    public const STRING_LOAD_MORE                    = 'stringLoadMore';
    public const STRING_MORNING_DELIVERY             = 'stringMorningDelivery';
    public const STRING_ONLY_RECIPIENT               = 'stringOnlyRecipient';
    public const STRING_OPENING_HOURS                = 'stringOpeningHours';
    public const STRING_PICKUP                       = 'stringPickup';
    public const STRING_PICKUP_LOCATIONS_LIST_BUTTON = 'stringPickupLocationsListButton';
    public const STRING_PICKUP_LOCATIONS_MAP_BUTTON  = 'stringPickupLocationsMapButton';
    public const STRING_POSTAL_CODE                  = 'stringPostalCode';
    public const STRING_RECIPIENT                    = 'stringRecipient';
    public const STRING_RETRY                        = 'stringRetry';
    public const STRING_SATURDAY_DELIVERY            = 'stringSaturdayDelivery';
    public const STRING_SIGNATURE                    = 'stringSignature';
    public const STRING_STANDARD_DELIVERY            = 'stringStandardDelivery';
    public const STRING_WRONG_NUMBER_POSTAL_CODE     = 'stringWrongNumberPostalCode';
    public const STRING_WRONG_POSTAL_CODE_CITY       = 'stringWrongPostalCodeCity';
    /**
     * All delivery options strings.
     */
    public const DELIVERY_OPTIONS_STRINGS      = [
        self::STRING_ADDRESS_NOT_FOUND,
        self::STRING_CITY,
        self::STRING_COUNTRY,
        self::STRING_DELIVERY,
        self::STRING_DISCOUNT,
        self::STRING_EVENING_DELIVERY,
        self::STRING_FROM,
        self::STRING_HOUSE_NUMBER,
        self::STRING_LOAD_MORE,
        self::STRING_MORNING_DELIVERY,
        self::STRING_ONLY_RECIPIENT,
        self::STRING_OPENING_HOURS,
        self::STRING_PICKUP,
        self::STRING_PICKUP_LOCATIONS_LIST_BUTTON,
        self::STRING_PICKUP_LOCATIONS_MAP_BUTTON,
        self::STRING_POSTAL_CODE,
        self::STRING_RECIPIENT,
        self::STRING_RETRY,
        self::STRING_SATURDAY_DELIVERY,
        self::STRING_SIGNATURE,
        self::STRING_STANDARD_DELIVERY,
        self::STRING_WRONG_NUMBER_POSTAL_CODE,
        self::STRING_WRONG_POSTAL_CODE_CITY,
    ];
    public const PICKUP_LOCATIONS_VIEW_MAP     = 'map';
    public const PICKUP_LOCATIONS_VIEW_LIST    = 'list';
    public const DEFAULT_PICKUP_LOCATIONS_VIEW = self::PICKUP_LOCATIONS_VIEW_LIST;

    protected $attributes = [
        self::DELIVERY_OPTIONS_CUSTOM_CSS   => null,
        self::DELIVERY_OPTIONS_DISPLAY      => false,
        self::DELIVERY_OPTIONS_POSITION     => null,
        self::PICKUP_LOCATIONS_DEFAULT_VIEW => self::DEFAULT_PICKUP_LOCATIONS_VIEW,
        self::PRICE_TYPE                    => null,
        self::SHOW_DELIVERY_DAY             => true,
        self::SHOW_PRICE_AS_SURCHARGE       => false,
        self::USE_SEPARATE_ADDRESS_FIELDS   => false,

        self::STRING_ADDRESS_NOT_FOUND            => null,
        self::STRING_COUNTRY                      => null,
        self::STRING_CITY                         => null,
        self::STRING_DELIVERY                     => null,
        self::STRING_DISCOUNT                     => null,
        self::STRING_EVENING_DELIVERY             => null,
        self::STRING_FROM                         => null,
        self::STRING_HOUSE_NUMBER                 => null,
        self::STRING_LOAD_MORE                    => null,
        self::STRING_MORNING_DELIVERY             => null,
        self::STRING_ONLY_RECIPIENT               => null,
        self::STRING_OPENING_HOURS                => null,
        self::STRING_PICKUP_LOCATIONS_LIST_BUTTON => null,
        self::STRING_PICKUP_LOCATIONS_MAP_BUTTON  => null,
        self::STRING_PICKUP                       => null,
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
        self::DELIVERY_OPTIONS_CUSTOM_CSS   => 'string',
        self::DELIVERY_OPTIONS_DISPLAY      => 'bool',
        self::DELIVERY_OPTIONS_POSITION     => 'string',
        self::PICKUP_LOCATIONS_DEFAULT_VIEW => 'string',
        self::PRICE_TYPE                    => 'string',
        self::SHOW_DELIVERY_DAY             => 'bool',
        self::SHOW_PRICE_AS_SURCHARGE       => 'bool',
        self::USE_SEPARATE_ADDRESS_FIELDS   => 'bool',

        self::STRING_ADDRESS_NOT_FOUND            => 'string',
        self::STRING_COUNTRY                      => 'string',
        self::STRING_CITY                         => 'string',
        self::STRING_DELIVERY                     => 'string',
        self::STRING_DISCOUNT                     => 'string',
        self::STRING_EVENING_DELIVERY             => 'string',
        self::STRING_FROM                         => 'string',
        self::STRING_HOUSE_NUMBER                 => 'string',
        self::STRING_LOAD_MORE                    => 'string',
        self::STRING_MORNING_DELIVERY             => 'string',
        self::STRING_ONLY_RECIPIENT               => 'string',
        self::STRING_OPENING_HOURS                => 'string',
        self::STRING_PICKUP_LOCATIONS_LIST_BUTTON => 'string',
        self::STRING_PICKUP_LOCATIONS_MAP_BUTTON  => 'string',
        self::STRING_PICKUP                       => 'string',
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
