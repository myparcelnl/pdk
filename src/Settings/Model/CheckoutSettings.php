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
 * @property string                                  $priceType
 * @property bool                                    $showDeliveryDay
 * @property bool                                    $useSeparateAddressFields
 */
class CheckoutSettings extends AbstractSettingsModel
{
    /**
     * Settings category ID.
     */
    final public const ID = 'checkout';
    /**
     * Settings in this category.
     */
    final public const ALLOWED_SHIPPING_METHODS                  = 'allowedShippingMethods';
    final public const DELIVERY_OPTIONS_CUSTOM_CSS               = 'deliveryOptionsCustomCss';
    final public const DELIVERY_OPTIONS_HEADER                   = 'deliveryOptionsHeader';
    final public const DELIVERY_OPTIONS_POSITION                 = 'deliveryOptionsPosition';
    final public const PICKUP_LOCATIONS_DEFAULT_VIEW             = 'pickupLocationsDefaultView';
    final public const PRICE_TYPE                                = 'priceType';
    final public const ENABLE_DELIVERY_OPTIONS                   = 'enableDeliveryOptions';
    final public const ENABLE_DELIVERY_OPTIONS_WHEN_NOT_IN_STOCK = 'enableDeliveryOptionsWhenNotInStock';
    final public const USE_SEPARATE_ADDRESS_FIELDS               = 'useSeparateAddressFields';
    /** Pickup location views */
    final public const PICKUP_LOCATIONS_VIEW_LIST = 'list';
    final public const PICKUP_LOCATIONS_VIEW_MAP  = 'map';
    /** Price types */
    final public const DEFAULT_PRICE_TYPE  = self::PRICE_TYPE_INCLUDED;
    final public const PRICE_TYPE_EXCLUDED = 'excluded';
    final public const PRICE_TYPE_INCLUDED = 'included';

    protected $attributes = [
        'id' => self::ID,

        self::ALLOWED_SHIPPING_METHODS                  => [],
        self::DELIVERY_OPTIONS_CUSTOM_CSS               => null,
        self::DELIVERY_OPTIONS_HEADER                   => null,
        self::DELIVERY_OPTIONS_POSITION                 => null,
        self::PICKUP_LOCATIONS_DEFAULT_VIEW             => null,
        self::PRICE_TYPE                                => self::DEFAULT_PRICE_TYPE,
        self::ENABLE_DELIVERY_OPTIONS                   => true,
        self::ENABLE_DELIVERY_OPTIONS_WHEN_NOT_IN_STOCK => true,
        self::USE_SEPARATE_ADDRESS_FIELDS               => false,
    ];

    protected $casts      = [
        self::ALLOWED_SHIPPING_METHODS                  => Collection::class,
        self::DELIVERY_OPTIONS_CUSTOM_CSS               => 'string',
        self::DELIVERY_OPTIONS_HEADER                   => 'string',
        self::DELIVERY_OPTIONS_POSITION                 => 'string',
        self::PICKUP_LOCATIONS_DEFAULT_VIEW             => 'string',
        self::PRICE_TYPE                                => 'string',
        self::ENABLE_DELIVERY_OPTIONS                   => 'bool',
        self::ENABLE_DELIVERY_OPTIONS_WHEN_NOT_IN_STOCK => 'bool',
        self::USE_SEPARATE_ADDRESS_FIELDS               => 'bool',
    ];
}
