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

