<?php

/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Sdk\Services\Mapping\ApiMapperService;

dataset('packageTypeNamesToIds', function () {
    $cases = [];

    foreach (ApiMapperService::forPackageType()->allRows() as $constant => $row) {
        if (null !== $row['legacy_name'] && null !== $row['id']) {
            $cases[$constant] = [$row['legacy_name'], $row['id']];
        }
    }

    return $cases;
});

dataset('packageTypeNames', function () {
    $cases = [];

    foreach (ApiMapperService::forPackageType()->allRows() as $constant => $row) {
        if (null !== $row['legacy_name'] && null !== $row['id']) {
            $cases[$constant] = [$row['legacy_name']];
        }
    }

    return $cases;
});

dataset('deliveryTypeNamesToIds', function () {
    $cases = [];

    foreach (ApiMapperService::forDeliveryType()->allRows() as $constant => $row) {
        if (null !== $row['legacy_name'] && null !== $row['id']) {
            $cases[$constant] = [$row['legacy_name'], $row['id']];
        }
    }

    return $cases;
});

dataset('deliveryTypeNames', function () {
    $cases = [];

    foreach (ApiMapperService::forDeliveryType()->allRows() as $constant => $row) {
        if (null !== $row['legacy_name'] && null !== $row['id']) {
            $cases[$constant] = [$row['legacy_name']];
        }
    }

    return $cases;
});

dataset('apiTypeMappings', function () {
    $cases   = [];
    $mappers = [
        'packageType'  => ApiMapperService::forPackageType(),
        'deliveryType' => ApiMapperService::forDeliveryType(),
    ];

    foreach ($mappers as $attribute => $mapper) {
        foreach ($mapper->allRows() as $constant => $row) {
            if (! in_array(null, $row, true)) {
                $cases[$attribute . ':' . $constant] = [$attribute, $row['id'], $row['legacy_name'], $row['v2_name']];
            }
        }
    }

    return $cases;
});

dataset('retailLocationTypes', function () {
    return array_map(
        static function (string $name) {
            return [$name];
        },
        \MyParcelNL\Pdk\Shipment\Model\RetailLocationType::ALL_TYPES
    );
});
