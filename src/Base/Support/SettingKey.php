<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Support;

use MyParcelNL\Sdk\Support\Str;

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
 * Inputs are normalised before the prefix is applied, so callers can pass
 * SDK V2 consts (SCREAMING_SNAKE_CASE), PDK *_NAME consts (snake_case), or
 * already-camelCase strings — all three resolve to the same attribute name.
 *
 * Centralising the formula here — and the handful of legacy exceptions —
 * keeps the prefixes in one place so callers don't drift apart.
 */
final class SettingKey
{
    /**
     * Legacy CarrierSettings allow-attribute names that do not follow the
     * 'allow' . ucfirst($key) formula. Keys are in normalised (camelCase) form.
     *
     * @var array<string, string>
     */
    private const ALLOW_EXCEPTIONS = [
        'pickupDelivery'  => 'allowPickupLocations',
        'expressDelivery' => 'allowDeliveryTypeExpress',
    ];

    /**
     * Legacy CarrierSettings pricePackageType-attribute names that do not
     * follow the 'pricePackageType' . ucfirst($key) formula. The SDK V2
     * package-type enum uses noun-second order (SMALL_PACKAGE) while the
     * carrier-settings attribute uses noun-first (packageSmall); the
     * mapping is encoded here so callers can pass SDK consts uniformly.
     *
     * @var array<string, string>
     */
    private const PRICE_PACKAGE_TYPE_EXCEPTIONS = [
        'smallPackage' => 'pricePackageTypePackageSmall',
    ];

    public static function allow(string $key): string
    {
        $normalized = self::normalize($key);

        return self::ALLOW_EXCEPTIONS[$normalized] ?? 'allow' . ucfirst($normalized);
    }

    public static function price(string $key): string
    {
        return 'price' . ucfirst(self::normalize($key));
    }

    public static function export(string $key): string
    {
        return 'export' . ucfirst(self::normalize($key));
    }

    /**
     * Existing CarrierSettings price-delivery-type attributes drop a trailing
     * "Delivery" (e.g. eveningDelivery → priceDeliveryTypeEvening) while the
     * matching allow-key keeps it (allowEveningDelivery). The legacy attribute
     * names are stored in installations and referenced from plugin snapshots,
     * so the suffix is stripped here to keep the helper output compatible.
     */
    public static function priceDeliveryType(string $key): string
    {
        $normalized = self::normalize($key);
        $suffix     = 'Delivery';

        if (substr($normalized, -strlen($suffix)) === $suffix && $normalized !== $suffix) {
            $normalized = substr($normalized, 0, -strlen($suffix));
        }

        return 'priceDeliveryType' . ucfirst($normalized);
    }

    public static function pricePackageType(string $key): string
    {
        $normalized = self::normalize($key);

        return self::PRICE_PACKAGE_TYPE_EXCEPTIONS[$normalized]
            ?? 'pricePackageType' . ucfirst($normalized);
    }

    /**
     * Normalise to camelCase. Accepts SDK V2 SCREAMING_SNAKE_CASE enum values,
     * PDK snake_case *_NAME consts, all-caps single words, and already-camelCase
     * strings (idempotent). Inputs starting with an uppercase letter are
     * lowercased first because Str::camel preserves internal casing.
     */
    private static function normalize(string $key): string
    {
        return $key !== '' && ctype_upper($key[0])
            ? Str::camel(strtolower($key))
            : Str::camel($key);
    }
}
