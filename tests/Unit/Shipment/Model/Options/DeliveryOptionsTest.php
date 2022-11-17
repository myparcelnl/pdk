<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\RetailLocation;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());
it('instantiates default delivery options', function () {
    $deliveryOptions = new DeliveryOptions();

    expect($deliveryOptions->date)
        ->toBeNull()
        ->and($deliveryOptions->deliveryType)
        ->toBe(DeliveryOptions::DEFAULT_DELIVERY_TYPE_NAME)
        ->and($deliveryOptions->packageType)
        ->toBe(DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME)
        ->and($deliveryOptions->pickupLocation)
        ->toBeNull()
        ->and($deliveryOptions->shipmentOptions->ageCheck)
        ->toBeNull()
        ->and($deliveryOptions->shipmentOptions->insurance)
        ->toBeNull()
        ->and($deliveryOptions->shipmentOptions->labelDescription)
        ->toBeNull()
        ->and($deliveryOptions->shipmentOptions->largeFormat)
        ->toBeNull()
        ->and($deliveryOptions->shipmentOptions->onlyRecipient)
        ->toBeNull()
        ->and($deliveryOptions->shipmentOptions->signature)
        ->toBeNull()
        ->and($deliveryOptions->isPickup())
        ->toBeFalse();
});

it('instantiates shipment options', function () {
    $deliveryOptions = new DeliveryOptions([
        'shipmentOptions' => [
            'insurance'        => 5000,
            'labelDescription' => 'hello',
            'ageCheck'         => true,
            'largeFormat'      => true,
            'onlyRecipient'    => true,
            'return'           => true,
            'sameDayDelivery'  => true,
            'signature'        => true,
        ],
    ]);

    expect($deliveryOptions->shipmentOptions->toArray())
        ->toEqual([
            'insurance'        => 5000,
            'labelDescription' => 'hello',
            'ageCheck'         => true,
            'largeFormat'      => true,
            'onlyRecipient'    => true,
            'return'           => true,
            'sameDayDelivery'  => true,
            'signature'        => true,
        ]);
});

it('instantiates delivery options with pickup location', function () {
    $deliveryOptions = new DeliveryOptions(
        [
            'date'           => '2022-02-20 16:00:00',
            'deliveryType'   => DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME,
            'pickupLocation' => new RetailLocation(['cc' => CountryCodes::CC_NL]),
        ]
    );

    expect($deliveryOptions->pickupLocation)
        ->toBeInstanceOf(RetailLocation::class)
        ->and($deliveryOptions->date)
        ->toBeInstanceOf(DateTimeImmutable::class)
        ->and($deliveryOptions->pickupLocation->cc)
        ->toBe(CountryCodes::CC_NL)
        ->and($deliveryOptions->isPickup())
        ->toBeTrue();
});

it('always returns an int as id', function () {
    $faultyDeliveryOptions = new DeliveryOptions(
        [
            'date'         => '2022-02-20 16:00:00',
            'deliveryType' => '1',
            'packageType'  => '2',
        ]
    );

    $faultyDeliveryOptions2 = new DeliveryOptions(
        [
            'date'         => '2022-02-20 16:00:00',
            'deliveryType' => 3,
            'packageType'  => 4,
        ]
    );

    expect($faultyDeliveryOptions->getDeliveryTypeId())
        ->toBe(1)
        ->and($faultyDeliveryOptions->getPackageTypeId())
        ->toBe(2)
        ->and($faultyDeliveryOptions2->getDeliveryTypeId())
        ->toBe(3)
        ->and($faultyDeliveryOptions2->getPackageTypeId())
        ->toBe(4);
});
