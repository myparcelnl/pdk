<?php

/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use DateTime;
use DateTimeImmutable;
use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Types\Service\TriStateService;
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
        ->and($deliveryOptions->shipmentOptions->labelDescription)
        ->toBeNull()
        ->and($deliveryOptions->shipmentOptions->ageCheck)
        ->toBe(TriStateService::INHERIT)
        ->and($deliveryOptions->shipmentOptions->insurance)
        ->toBe(TriStateService::INHERIT)
        ->and($deliveryOptions->shipmentOptions->largeFormat)
        ->toBe(TriStateService::INHERIT)
        ->and($deliveryOptions->shipmentOptions->onlyRecipient)
        ->toBe(TriStateService::INHERIT)
        ->and($deliveryOptions->shipmentOptions->signature)
        ->toBe(TriStateService::INHERIT)
        ->and($deliveryOptions->isPickup())
        ->toBeFalse();
});

it('instantiates shipment options', function () {
    $deliveryOptions = new DeliveryOptions([
        'shipmentOptions' => [
            'insurance'            => 5000,
            'labelDescription'     => 'hello',
            'ageCheck'             => true,
            'hideSender'           => true,
            'largeFormat'          => true,
            'onlyRecipient'        => true,
            'return'               => true,
            'sameDayDelivery'      => true,
            'signature'            => true,
            'receiptCode'          => true,
            'collect'              => true,
            'excludeParcelLockers' => false,
            'priorityDelivery'     => true,
            'frozen'               => false,
            'freshFood'             => false,
        ],
    ]);

    expect($deliveryOptions->shipmentOptions->toArray())
        ->toEqual([
            'insurance'        => 5000,
            'labelDescription' => 'hello',
            'ageCheck'         => TriStateService::ENABLED,
            'hideSender'       => TriStateService::ENABLED,
            'largeFormat'      => TriStateService::ENABLED,
            'onlyRecipient'    => TriStateService::ENABLED,
            'return'           => TriStateService::ENABLED,
            'sameDayDelivery'  => TriStateService::ENABLED,
            'signature'        => TriStateService::ENABLED,
            'tracked'          => TriStateService::INHERIT,
            'receiptCode'      => TriStateService::ENABLED,
            'collect'          => TriStateService::ENABLED,
            'excludeParcelLockers' => TriStateService::DISABLED,
            'priorityDelivery' => TriStateService::ENABLED,
            'frozen'           => TriStateService::DISABLED,
            'freshFood'        => TriStateService::DISABLED,
            'saturdayDelivery' => TriStateService::INHERIT
        ]);
});

it('instantiates delivery options with pickup location', function () {
    $deliveryOptions = new DeliveryOptions(
        [
            'date'           => new DateTime('+1 day'),
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

it('does not return a date in the past', function () {
    $deliveryOptions = new DeliveryOptions(['date' => new DateTime('-1 day')]);

    expect($deliveryOptions->date)->toBeNull();
});

it('converts input package type to name', function (string $name, int $id) {
    $deliveryOptions = new DeliveryOptions(['packageType' => $id]);

    expect($deliveryOptions->packageType)->toBe($name);
})->with('packageTypeNamesToIds');

it('can get package type as id', function (string $name, int $id) {
    $deliveryOptions = new DeliveryOptions(['packageType' => $name]);

    expect($deliveryOptions->getPackageTypeId())->toBe($id);
})->with('packageTypeNamesToIds');

it('converts input delivery type to name', function (string $name, int $id) {
    $deliveryOptions = new DeliveryOptions(['deliveryType' => $id]);

    expect($deliveryOptions->deliveryType)->toBe($name);
})->with('deliveryTypeNamesToIds');

it('can get delivery type as id', function (string $name, int $id) {
    $deliveryOptions = new DeliveryOptions(['deliveryType' => $name]);

    expect($deliveryOptions->getDeliveryTypeId())->toBe($id);
})->with('deliveryTypeNamesToIds');

it('can be instantiated from its storable array', function () {
    $carrier = new Carrier([
        'externalIdentifier' => 'dhlforyou:8277',
    ]);

    $original = new DeliveryOptions([
        'carrier'         => $carrier,
        'packageType'     => 'package',
        'deliveryType'    => 'delivery',
        'date'            => '2022-02-20 16:00:00',
        'pickupLocation'  => ['cc' => CountryCodes::CC_NL],
        'shipmentOptions' => [
            'insurance'        => 5000,
            'labelDescription' => 'hello',
            'ageCheck'         => true,
            'hideSender'       => true,
            'largeFormat'      => true,
            'onlyRecipient'    => true,
            'return'           => true,
            'sameDayDelivery'  => true,
            'signature'        => true,
        ],
    ]);

    $fromStorable = new DeliveryOptions($original->toStorableArray());

    expect($original->toArrayWithoutNull())->toEqual($fromStorable->toArrayWithoutNull());
});
