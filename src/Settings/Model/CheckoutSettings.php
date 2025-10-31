<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

use MyParcelNL\Pdk\Base\Support\Collection;

/**
 * @property \MyParcelNL\Pdk\Base\Support\Collection $allowedShippingMethods
 * @property string                                  $deliveryOptionsCustomCss
 * @property string                                  $deliveryOptionsHeader
 * @property string                                  $deliveryOptionsPosition
 * @property string                                  $pickupLocationsDefaultView
 * @property bool                                    $allowPickupLocationsViewSelection
 * @property string                                  $priceType
 * @property bool                                    $showDeliveryDay
 * @property bool                                    $useSeparateAddressFields
 * @property bool                                    $enableAddressWidget
 * @property bool                                    $excludeParcelLockers
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
    public const ALLOWED_SHIPPING_METHODS                  = 'allowedShippingMethods';
    public const DELIVERY_OPTIONS_CUSTOM_CSS               = 'deliveryOptionsCustomCss';
    public const DELIVERY_OPTIONS_HEADER                   = 'deliveryOptionsHeader';
    public const DELIVERY_OPTIONS_POSITION                 = 'deliveryOptionsPosition';
    public const PICKUP_LOCATIONS_DEFAULT_VIEW             = 'pickupLocationsDefaultView';
    public const ALLOW_PICKUP_LOCATIONS_VIEW_SELECTION     = 'allowPickupLocationsViewSelection';
    public const PRICE_TYPE                                = 'priceType';
    public const ENABLE_DELIVERY_OPTIONS                   = 'enableDeliveryOptions';
    public const ENABLE_DELIVERY_OPTIONS_WHEN_NOT_IN_STOCK = 'enableDeliveryOptionsWhenNotInStock';
    public const USE_SEPARATE_ADDRESS_FIELDS               = 'useSeparateAddressFields';
    /** Pickup location views */
    public const PICKUP_LOCATIONS_VIEW_LIST = 'list';
    public const PICKUP_LOCATIONS_VIEW_MAP  = 'map';
    /** Price types */
    public const DEFAULT_PRICE_TYPE  = self::PRICE_TYPE_INCLUDED;
    public const PRICE_TYPE_EXCLUDED = 'excluded';
    public const PRICE_TYPE_INCLUDED = 'included';
    public const SHOW_TAX_FIELDS     = 'showTaxFields';
    /** Address widget */
    public const ENABLE_ADDRESS_WIDGET = 'enableAddressWidget';
    public const CLOSED_DAYS           = 'closedDays';
    /** Parcel lockers */
    public const EXCLUDE_PARCEL_LOCKERS = 'excludeParcelLockers';

    protected $attributes = [
        'id' => self::ID,

        self::ALLOWED_SHIPPING_METHODS                  => [],
        self::DELIVERY_OPTIONS_CUSTOM_CSS               => null,
        self::DELIVERY_OPTIONS_HEADER                   => null,
        self::DELIVERY_OPTIONS_POSITION                 => null,
        self::PICKUP_LOCATIONS_DEFAULT_VIEW             => null,
        self::ALLOW_PICKUP_LOCATIONS_VIEW_SELECTION     => true,
        self::PRICE_TYPE                                => self::DEFAULT_PRICE_TYPE,
        self::ENABLE_DELIVERY_OPTIONS                   => true,
        self::ENABLE_DELIVERY_OPTIONS_WHEN_NOT_IN_STOCK => true,
        self::USE_SEPARATE_ADDRESS_FIELDS               => false,
        self::SHOW_TAX_FIELDS                           => true,
        self::ENABLE_ADDRESS_WIDGET                     => false,
        self::CLOSED_DAYS                               => [],
        self::EXCLUDE_PARCEL_LOCKERS                    => false,
    ];

    protected $casts      = [
        self::ALLOWED_SHIPPING_METHODS                  => Collection::class,
        self::DELIVERY_OPTIONS_CUSTOM_CSS               => 'string',
        self::DELIVERY_OPTIONS_HEADER                   => 'string',
        self::DELIVERY_OPTIONS_POSITION                 => 'string',
        self::PICKUP_LOCATIONS_DEFAULT_VIEW             => 'string',
        self::ALLOW_PICKUP_LOCATIONS_VIEW_SELECTION     => 'bool',
        self::PRICE_TYPE                                => 'string',
        self::ENABLE_DELIVERY_OPTIONS                   => 'bool',
        self::ENABLE_DELIVERY_OPTIONS_WHEN_NOT_IN_STOCK => 'bool',
        self::USE_SEPARATE_ADDRESS_FIELDS               => 'bool',
        self::SHOW_TAX_FIELDS                           => 'bool',
        self::ENABLE_ADDRESS_WIDGET                     => 'bool',
        self::CLOSED_DAYS                               => 'array',
        self::EXCLUDE_PARCEL_LOCKERS                    => 'bool',
    ];
}
