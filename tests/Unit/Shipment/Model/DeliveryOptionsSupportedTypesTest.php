<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use MyParcelNL\Sdk\Services\Mapping\ApiMapperService;

it('supports SDK types only when both a model name and export ID exist', function () {
    $mappers = [
        'isPackageTypeSupported'  => ApiMapperService::forPackageType(),
        'isDeliveryTypeSupported' => ApiMapperService::forDeliveryType(),
    ];

    foreach ($mappers as $method => $mapper) {
        foreach ($mapper->allRows() as $row) {
            if (null === $row['v2_name']) {
                continue;
            }

            $expected = null !== $row['legacy_name'] && null !== $row['id'];

            expect(DeliveryOptions::$method($row['v2_name']))->toBe($expected);
        }
    }
});

it('does not expose unknown types', function (string $value) {
    expect(DeliveryOptions::isDeliveryTypeSupported($value))->toBeFalse()
        ->and(DeliveryOptions::isPackageTypeSupported($value))->toBeFalse();
})->with(['UNKNOWN_DELIVERY', 'UNKNOWN_PACKAGE', '']);
