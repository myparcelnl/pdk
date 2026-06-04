<?php

/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;

dataset('packageTypeNamesToIds', function () {
    $map = DeliveryOptions::PACKAGE_TYPES_NAMES_IDS_MAP;

    return array_map(
        static function (string $name) use ($map) {
            return [$name, $map[$name]];
        },
        array_keys($map)
    );
});

dataset('deliveryTypeNamesToIds', function () {
    $map = DeliveryOptions::DELIVERY_TYPES_NAMES_IDS_MAP;

    return array_map(
        static function (string $name) use ($map) {
            return [$name, $map[$name]];
        },
        array_keys($map)
    );
});

dataset('packageTypeNames', function () {
    return array_map(
        static function (string $name) {
            return [$name];
        },
        array_keys(DeliveryOptions::PACKAGE_TYPES_V2_MAP)
    );
});

dataset('deliveryTypeNames', function () {
    return array_map(
        static function (string $name) {
            return [$name];
        },
        array_keys(DeliveryOptions::DELIVERY_TYPES_V2_MAP)
    );
});

dataset('retailLocationTypes', function () {
    return array_map(
        static function (string $name) {
            return [$name];
        },
        \MyParcelNL\Pdk\Shipment\Model\RetailLocationType::ALL_TYPES
    );
});
