<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

/**
 * @property string $deliveryOptionsCustomCss
 * @property bool   $deliveryOptionsDisplay
 * @property string $deliveryOptionsHeader
 * @property string $deliveryOptionsPosition
 * @property string $pickupLocationsDefaultView
 * @property string $priceType
 * @property bool   $showDeliveryDay
 * @property bool   $useSeparateAddressFields
 */
class CheckoutSettings extends AbstractSettingsModel
{
    /**
     * Settings category ID.
     */
    public const ID = 'checkout';
    /**
     * Settings in this category.
     */
    public const DELIVERY_OPTIONS_CUSTOM_CSS   = 'deliveryOptionsCustomCss';
    public const DELIVERY_OPTIONS_DISPLAY      = 'deliveryOptionsDisplay';
    public const DELIVERY_OPTIONS_HEADER       = 'deliveryOptionsHeader';
    public const DELIVERY_OPTIONS_POSITION     = 'deliveryOptionsPosition';
    public const PICKUP_LOCATIONS_DEFAULT_VIEW = 'pickupLocationsDefaultView';
    public const PRICE_TYPE                    = 'priceType';
    public const SHOW_DELIVERY_DAY             = 'showDeliveryDay';
    public const USE_SEPARATE_ADDRESS_FIELDS   = 'useSeparateAddressFields';
    public const EMPTY_WEIGHT_MAILBOX          = 'emptyWeightMailbox';
    public const EMPTY_WEIGHT_PACKAGE          = 'emptyWeightPackage';
    /** Pickup location views */
    public const PICKUP_LOCATIONS_VIEW_LIST = 'list';
    public const PICKUP_LOCATIONS_VIEW_MAP  = 'map';
    /** Price types */
    public const DEFAULT_PRICE_TYPE  = self::PRICE_TYPE_INCLUDED;
    public const PRICE_TYPE_EXCLUDED = 'excluded';
    public const PRICE_TYPE_INCLUDED = 'included';

    protected $attributes = [
        'id' => self::ID,

        self::DELIVERY_OPTIONS_CUSTOM_CSS   => null,
        self::DELIVERY_OPTIONS_DISPLAY      => false,
        self::DELIVERY_OPTIONS_HEADER       => null,
        self::DELIVERY_OPTIONS_POSITION     => null,
        self::PICKUP_LOCATIONS_DEFAULT_VIEW => null,
        self::PRICE_TYPE                    => self::DEFAULT_PRICE_TYPE,
        self::SHOW_DELIVERY_DAY             => true,
        self::USE_SEPARATE_ADDRESS_FIELDS   => false,
        self::EMPTY_WEIGHT_MAILBOX          => 0,
        self::EMPTY_WEIGHT_PACKAGE          => 0,
    ];

    protected $casts      = [
        self::DELIVERY_OPTIONS_CUSTOM_CSS   => 'string',
        self::DELIVERY_OPTIONS_DISPLAY      => 'bool',
        self::DELIVERY_OPTIONS_HEADER       => 'string',
        self::DELIVERY_OPTIONS_POSITION     => 'string',
        self::PICKUP_LOCATIONS_DEFAULT_VIEW => 'string',
        self::PRICE_TYPE                    => 'string',
        self::SHOW_DELIVERY_DAY             => 'bool',
        self::USE_SEPARATE_ADDRESS_FIELDS   => 'bool',
        self::EMPTY_WEIGHT_MAILBOX          => 'int',
        self::EMPTY_WEIGHT_PACKAGE          => 'int',
    ];
}
