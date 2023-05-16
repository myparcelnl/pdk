<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

use MyParcelNL\Pdk\Base\Support\Collection;

/**
 * @property \MyParcelNL\Pdk\Base\Support\Collection $allowedShippingMethods
 * @property string                                  $deliveryOptionsCustomCss
 * @property bool                                    $deliveryOptionsDisplay
 * @property string                                  $deliveryOptionsHeader
 * @property string                                  $deliveryOptionsPosition
 * @property string                                  $pickupLocationsDefaultView
 * @property string                                  $priceType
 * @property bool                                    $showDeliveryDay
 * @property bool                                    $useSeparateAddressFields
 */
class CheckoutSettings extends AbstractSettingsModel
{
    /**
     * Settings in this category.
     */
    public const ALLOWED_SHIPPING_METHODS = 'allowedShippingMethods';
    /** Price types */
    public const DEFAULT_PRICE_TYPE                        = self::PRICE_TYPE_INCLUDED;
    public const DELIVERY_OPTIONS_CUSTOM_CSS               = 'deliveryOptionsCustomCss';
    public const DELIVERY_OPTIONS_DISPLAY                  = 'deliveryOptionsDisplay';
    public const DELIVERY_OPTIONS_HEADER                   = 'deliveryOptionsHeader';
    public const DELIVERY_OPTIONS_POSITION                 = 'deliveryOptionsPosition';
    public const ENABLE_DELIVERY_OPTIONS                   = 'enableDeliveryOptions';
    public const ENABLE_DELIVERY_OPTIONS_WHEN_NOT_IN_STOCK = 'enableDeliveryOptionsWhenNotInStock';
    public const EXPORT_INSURANCE_PRICE_FACTOR             = 'insurancePriceFactor';
    /**
     * Settings category ID.
     */
    public const ID                            = 'checkout';
    public const PICKUP_LOCATIONS_DEFAULT_VIEW = 'pickupLocationsDefaultView';
    /** Pickup location views */
    public const PICKUP_LOCATIONS_VIEW_LIST  = 'list';
    public const PICKUP_LOCATIONS_VIEW_MAP   = 'map';
    public const PRICE_TYPE                  = 'priceType';
    public const PRICE_TYPE_EXCLUDED         = 'excluded';
    public const PRICE_TYPE_INCLUDED         = 'included';
    public const USE_SEPARATE_ADDRESS_FIELDS = 'useSeparateAddressFields';

    protected $attributes = [
        'id' => self::ID,

        self::ALLOWED_SHIPPING_METHODS                  => [],
        self::DELIVERY_OPTIONS_CUSTOM_CSS               => null,
        self::DELIVERY_OPTIONS_DISPLAY                  => false,
        self::DELIVERY_OPTIONS_HEADER                   => null,
        self::DELIVERY_OPTIONS_POSITION                 => null,
        self::PICKUP_LOCATIONS_DEFAULT_VIEW             => null,
        self::PRICE_TYPE                                => self::DEFAULT_PRICE_TYPE,
        self::ENABLE_DELIVERY_OPTIONS                   => true,
        self::ENABLE_DELIVERY_OPTIONS_WHEN_NOT_IN_STOCK => true,
        self::EXPORT_INSURANCE_PRICE_FACTOR             => 1,
        self::USE_SEPARATE_ADDRESS_FIELDS               => false,
    ];

    protected $casts      = [
        self::ALLOWED_SHIPPING_METHODS                  => Collection::class,
        self::DELIVERY_OPTIONS_CUSTOM_CSS               => 'string',
        self::DELIVERY_OPTIONS_DISPLAY                  => 'bool',
        self::DELIVERY_OPTIONS_HEADER                   => 'string',
        self::DELIVERY_OPTIONS_POSITION                 => 'string',
        self::PICKUP_LOCATIONS_DEFAULT_VIEW             => 'string',
        self::PRICE_TYPE                                => 'string',
        self::ENABLE_DELIVERY_OPTIONS                   => 'bool',
        self::ENABLE_DELIVERY_OPTIONS_WHEN_NOT_IN_STOCK => 'bool',
        self::EXPORT_INSURANCE_PRICE_FACTOR             => 'float',
        self::USE_SEPARATE_ADDRESS_FIELDS               => 'bool',
    ];
}
