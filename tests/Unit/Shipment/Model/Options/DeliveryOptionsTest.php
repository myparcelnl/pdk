<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Data\CountryCodes;
use MyParcelNL\Pdk\Shipment\Model\Options\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\Options\PickupLocation;

it('instantiates default delivery options', function () {
    $deliveryOptions = new DeliveryOptions();

    expect($deliveryOptions->date)
        ->toBeNull()
        ->and($deliveryOptions->deliveryType)
        ->toBeNull()
        ->and($deliveryOptions->packageType)
        ->toBeNull()
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

it('instantiates delivery options with pickup location', function () {
    $deliveryOptions = new DeliveryOptions(
        [
            'date'           => '2022-02-20 16:00:00',
            'deliveryType'   => DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME,
            'pickupLocation' => new PickupLocation(['cc' => CountryCodes::CC_NL]),
        ]
    );

    expect($deliveryOptions->pickupLocation)
        ->toBeInstanceOf(PickupLocation::class)
        ->and($deliveryOptions->date)
        ->toBeInstanceOf(DateTime::class)
        ->and($deliveryOptions->pickupLocation->cc)
        ->toBe(CountryCodes::CC_NL)
        ->and($deliveryOptions->isPickup())
        ->toBeTrue();
});
