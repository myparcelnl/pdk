<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefShipmentPackageTypeV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefTypesDeliveryTypeV2;

use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('reports support only for delivery types known to this PDK version', function () {
    expect(DeliveryOptions::isDeliveryTypeSupported(RefTypesDeliveryTypeV2::STANDARD))->toBeTrue()
        ->and(DeliveryOptions::isDeliveryTypeSupported(RefTypesDeliveryTypeV2::SAME_DAY))->toBeTrue()
        ->and(DeliveryOptions::isDeliveryTypeSupported(RefTypesDeliveryTypeV2::EARLY_MORNING))->toBeTrue()
        ->and(DeliveryOptions::isDeliveryTypeSupported('UNKNOWN_DELIVERY'))->toBeFalse()
        ->and(DeliveryOptions::isDeliveryTypeSupported(''))->toBeFalse();
});

it('reports support only for package types known to this PDK version', function () {
    expect(DeliveryOptions::isPackageTypeSupported(RefShipmentPackageTypeV2::PACKAGE))->toBeTrue()
        ->and(DeliveryOptions::isPackageTypeSupported(RefShipmentPackageTypeV2::PALLET))->toBeTrue()
        ->and(DeliveryOptions::isPackageTypeSupported(RefShipmentPackageTypeV2::ENVELOPE))->toBeTrue()
        ->and(DeliveryOptions::isPackageTypeSupported('UNKNOWN_PACKAGE'))->toBeFalse()
        ->and(DeliveryOptions::isPackageTypeSupported(''))->toBeFalse();
});
