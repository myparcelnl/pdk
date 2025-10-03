<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Account\Platform;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Frontend\Contract\FrontendDataAdapterInterface;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;

beforeEach(function () {
    TestBootstrapper::forPlatform(Platform::MYPARCEL_NAME);
});

it('returns legacy package types', function () {
    $adapter = Pdk::get(FrontendDataAdapterInterface::class);

    $types = $adapter->getLegacyPackageTypes();

    expect($types)->toEqual(DeliveryOptions::PACKAGE_TYPES_NAMES);
});

it('returns legacy delivery types', function () {
    $adapter = Pdk::get(FrontendDataAdapterInterface::class);

    $types = $adapter->getLegacyDeliveryTypes();

    expect($types)->toEqual(DeliveryOptions::DELIVERY_TYPES_NAMES);
});

it('returns legacy shipment options', function () {
    $adapter = Pdk::get(FrontendDataAdapterInterface::class);

    $options = $adapter->getLegacyShipmentOptions();

    expect($options)->not->toBeEmpty();
    expect($options)->each->toBeIn(ShipmentOptions::ALL_SHIPMENT_OPTIONS);
});
