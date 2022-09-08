<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property bool   $showPriceSurcharge
 * @property string $pickupLocationsDefaultView
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
    public const SHOW_PRICE_SURCHARGE          = 'showPriceSurcharge';
    public const PICKUP_LOCATIONS_DEFAULT_VIEW = 'pickupLocationsDefaultView';
    public const STRINGS                       = 'strings';
    /**
     * Values
     */
    public const PICKUP_LOCATIONS_DEFAULT_VIEW_MAP  = 'map';
    public const PICKUP_LOCATIONS_DEFAULT_VIEW_LIST = 'list';

    protected $attributes = [
        self::SHOW_PRICE_SURCHARGE          => false,
        self::PICKUP_LOCATIONS_DEFAULT_VIEW => self::PICKUP_LOCATIONS_DEFAULT_VIEW_MAP,
        self::STRINGS                       => DeliveryOptionsStringsSettings::class,
    ];

    protected $casts      = [
        self::SHOW_PRICE_SURCHARGE          => 'bool',
        self::PICKUP_LOCATIONS_DEFAULT_VIEW => 'string',
        self::STRINGS                       => DeliveryOptionsStringsSettings::class,
    ];
}
