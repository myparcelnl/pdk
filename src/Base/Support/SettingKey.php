<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Support;

/**
 * Builds CarrierSettings attribute keys from a shipment-option, delivery-type
 * or package-type identifier.
 *
 * Carrier-level settings are organised along five axes for each option:
 *  - allow*              — consumer-facing toggle (delivery-options widget)
 *  - price*              — surcharge shown to the consumer
 *  - export*             — merchant-side default applied when exporting
 *  - priceDeliveryType*  — surcharge per delivery type
 *  - pricePackageType*   — surcharge per package type
 *
 * Centralising the formula here keeps the prefixes — and the handful of legacy
 * exceptions to the formula — in one place so callers don't drift apart.
 */
final class SettingKey
{
    /**
     * Legacy CarrierSettings attribute names that do not follow the
     * 'allow' . ucfirst($key) formula. Kept here so the irregular mapping has
     * exactly one home; entries can be removed once each underlying attribute
     * has been renamed and stored merchant data has been migrated.
     *
     * @var array<string, string>
     */
    private const ALLOW_EXCEPTIONS = [
        'pickupDelivery' => 'allowPickupLocations',
    ];

    public static function allow(string $key): string
    {
        return self::ALLOW_EXCEPTIONS[$key] ?? 'allow' . ucfirst($key);
    }

    public static function price(string $key): string
    {
        return 'price' . ucfirst($key);
    }

    public static function export(string $key): string
    {
        return 'export' . ucfirst($key);
    }

    /**
     * Existing CarrierSettings price-delivery-type attributes drop a trailing
     * "Delivery" (e.g. eveningDelivery → priceDeliveryTypeEvening) while the
     * matching allow-key keeps it (allowEveningDelivery). The legacy attribute
     * names are stored in installations and referenced from plugin snapshots,
     * so the suffix is stripped here to keep the dynamic loop compatible.
     */
    public static function priceDeliveryType(string $key): string
    {
        $suffix = 'Delivery';

        if (substr($key, -strlen($suffix)) === $suffix && $key !== $suffix) {
            $key = substr($key, 0, -strlen($suffix));
        }

        return 'priceDeliveryType' . ucfirst($key);
    }

    public static function pricePackageType(string $key): string
    {
        return 'pricePackageType' . ucfirst($key);
    }
}
