<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Unit\App\Endpoint\Resource;

use ArrayObject;
use MyParcelNL\Pdk\App\Endpoint\Resource\DeliveryOptionsV1Resource;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Proposition\Model\PropositionCarrierFeatures;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\RetailLocation;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use MyParcelNL\Sdk\Model\PickupLocation;

use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('formats delivery options correctly', function () {
    $shipmentOptions = new ShipmentOptions([
        'signature' => TriStateService::ENABLED,
        'onlyRecipient' => TriStateService::ENABLED,
        'return' => TriStateService::DISABLED,
        'largeFormat' => TriStateService::ENABLED,
        'ageCheck' => TriStateService::ENABLED,
        'insurance' => 50000,
    ]);

    $carrier = new Carrier(['name' => 'postnl']);

    $deliveryOptions = new DeliveryOptions([
        'carrier' => $carrier,
        'packageType' => 'package',
        'deliveryType' => 'standard',
        'shipmentOptions' => $shipmentOptions,
    ]);

    $resource = new DeliveryOptionsV1Resource($deliveryOptions);
    $result = $resource->format();

    expect($result)
        ->toHaveKey('carrier')
        ->toHaveKey('packageType')
        ->toHaveKey('deliveryType')
        ->toHaveKey('shipmentOptions')
        ->and($result['shipmentOptions'])
        ->toBeArray()
        ->toHaveKey('requiresSignature')
        ->toHaveKey('recipientOnlyDelivery')
        ->toHaveKey('oversizedPackage')
        ->toHaveKey('requiresAgeVerification')
        ->toHaveKey('insurance')
        ->not()->toHaveKey('printReturnLabelAtDropOff');

    // Check that regular options are empty objects
    expect($result['shipmentOptions']['requiresSignature'])->toBeInstanceOf(ArrayObject::class);
    expect($result['shipmentOptions']['recipientOnlyDelivery'])->toBeInstanceOf(ArrayObject::class);
    expect($result['shipmentOptions']['oversizedPackage'])->toBeInstanceOf(ArrayObject::class);
    expect($result['shipmentOptions']['requiresAgeVerification'])->toBeInstanceOf(ArrayObject::class);

    // Check that insurance has amount in micro units
    expect($result['shipmentOptions']['insurance'])
        ->toBeArray()
        ->toHaveKey('amount', 50000 * 1000000);
});

it('returns empty object when no shipment options are enabled', function () {
    $shipmentOptions = new ShipmentOptions([
        'signature' => TriStateService::DISABLED,
        'onlyRecipient' => TriStateService::DISABLED,
        'return' => TriStateService::DISABLED,
        'largeFormat' => TriStateService::DISABLED,
        'ageCheck' => TriStateService::DISABLED,
        'insurance' => 0,
    ]);

    $deliveryOptions = new DeliveryOptions([
        'shipmentOptions' => $shipmentOptions,
    ]);

    $resource = new DeliveryOptionsV1Resource($deliveryOptions);
    $result = $resource->format();

    expect($result['shipmentOptions'])
        ->toBeArray()
        ->toBeEmpty();
});

it('correctly formats insurance amount in micro units', function () {
    $shipmentOptions = new ShipmentOptions([
        'insurance' => 100, // €100
    ]);

    $deliveryOptions = new DeliveryOptions([
        'shipmentOptions' => $shipmentOptions,
    ]);

    $resource = new DeliveryOptionsV1Resource($deliveryOptions);
    $result = $resource->format();

    expect($result['shipmentOptions'])
        ->toHaveKey('insurance')
        ->and($result['shipmentOptions']['insurance'])
        ->toBeArray()
        ->toHaveKey('amount', 100 * 1000000);
});

it('ignores inherited shipment options', function () {
    $shipmentOptions = new ShipmentOptions([
        'signature' => TriStateService::INHERIT,
        'onlyRecipient' => TriStateService::ENABLED,
        'largeFormat' => TriStateService::INHERIT,
        'insurance' => 50,
    ]);

    $deliveryOptions = new DeliveryOptions([
        'shipmentOptions' => $shipmentOptions,
    ]);

    $resource = new DeliveryOptionsV1Resource($deliveryOptions);
    $result = $resource->format();

    expect($result['shipmentOptions'])
        ->toHaveKey('recipientOnlyDelivery')
        ->toHaveKey('insurance')
        ->not()->toHaveKey('requiresSignature')
        ->not()->toHaveKey('oversizedPackage');

    expect($result['shipmentOptions']['recipientOnlyDelivery'])->toBeInstanceOf(ArrayObject::class);
});

it('declares version 1', function () {
    expect(DeliveryOptionsV1Resource::getVersion())->toBe(1);
});

it('creates response with versioned headers', function () {
    $deliveryOptions = new DeliveryOptions();
    $resource = new DeliveryOptionsV1Resource($deliveryOptions);
    $request = new \Symfony\Component\HttpFoundation\Request();

    $response = $resource->createResponse($request);

    expect($response->getStatusCode())->toBe(200);
    expect($response->headers->get('Content-Type'))->toContain('version=1');
});

it('formats date as ISO 8601 string', function () {
    $deliveryOptions = new DeliveryOptions([
        'date' => new \DateTime('2026-03-15T10:30:00+00:00'),
    ]);

    $resource = new DeliveryOptionsV1Resource($deliveryOptions);
    $result = $resource->format();

    expect($result)->toHaveKey('date');
    expect($result['date'])->toMatch('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/');
});

it('formats date as null when not set', function () {
    $deliveryOptions = new DeliveryOptions();

    $resource = new DeliveryOptionsV1Resource($deliveryOptions);
    $result = $resource->format();

    expect($result)->toHaveKey('date');
    expect($result['date'])->toBeNull();
});

it('correctly returns a pickup location when applicable', function () {
    $carrier = new Carrier([
        'name' => 'postnl',
        'propositionCarrierFeatures' => new PropositionCarrierFeatures([
            'pickupLocations' => true,
        ]),
    ]);

    $deliveryOptions = new DeliveryOptions([
        'carrier' => $carrier,
        'deliveryType' => 'pickup',
    ]);

    $deliveryOptions->pickupLocation = new RetailLocation([
        'locationCode'    => 'LOC123',
        'locationName'    => 'Main Street Pickup',
        'retailNetworkId' => 'RN001',
        'city'            => 'Amsterdam',
        'postalCode'      => '1000 AA',
        'street'          => 'Main Street',
        'number'          => '1',
        'numberSuffix'    => 'A',
        'cc'              => 'NL',
        'boxNumber'       => '123',
        'state'          => 'North Holland',
        'region'         => 'RegionX',
    ]);

    $resource = new DeliveryOptionsV1Resource($deliveryOptions);
    $result = $resource->format();

    expect($result)->toHaveKey('pickupLocation');
    expect($result['pickupLocation'])->toEqualCanonicalizing([
        'locationCode'    => 'LOC123',
        'locationName'    => 'Main Street Pickup',
        'retailNetworkId' => 'RN001',
        'type'            => null,
        'address' => [
            'city'            => 'Amsterdam',
            'postalCode'      => '1000 AA',
            'street'          => 'Main Street',
            'number'          => '1',
            'numberSuffix'    => 'A',
            'cc'              => 'NL',
            'boxNumber'       => '123',
            'state'          => 'North Holland',
            'region'         => 'RegionX',
        ]
    ]);
});

it('maps carrier names using direct carrier mapping', function () {
    $carriers = [
        'POSTNL' => 'POSTNL',
        'BPOST' => 'BPOST',
        'CHEAP_CARGO' => 'CHEAP_CARGO',
        'DPD' => 'DPD',
        'BOL' => 'BOL',
        'DHL_FOR_YOU' => 'DHL_FOR_YOU',
        'DHL_PARCEL_CONNECT' => 'DHL_PARCEL_CONNECT',
        'DHL_EUROPLUS' => 'DHL_EUROPLUS',
        'UPS_STANDARD' => 'UPS_STANDARD',
        'UPS_EXPRESS_SAVER' => 'UPS_EXPRESS_SAVER',
        'GLS' => 'GLS',
        'BRT' => 'BRT',
        'TRUNKRS' => 'TRUNKRS',
    ];

    foreach ($carriers as $carrierName => $expected) {
        $carrier = new Carrier(['name' => $carrierName]);
        $deliveryOptions = new DeliveryOptions(['carrier' => $carrier]);
        $resource = new DeliveryOptionsV1Resource($deliveryOptions);
        $result = $resource->format();

        expect($result['carrier'])->toBe($expected);
    }
});

it('maps package types using direct mapping', function () {
    $packageTypes = [
        'package' => 'PACKAGE',
        'mailbox' => 'MAILBOX',
        'letter' => 'UNFRANKED',
        'digital_stamp' => 'DIGITAL_STAMP',
        'package_small' => 'SMALL_PACKAGE',
    ];

    foreach ($packageTypes as $packageType => $expected) {
        $deliveryOptions = new DeliveryOptions(['packageType' => $packageType]);
        $resource = new DeliveryOptionsV1Resource($deliveryOptions);
        $result = $resource->format();

        expect($result['packageType'])->toBe($expected);
    }
});

it('maps delivery types using direct mapping', function () {
    $deliveryTypes = [
        'standard' => 'STANDARD_DELIVERY',
        'morning' => 'MORNING_DELIVERY',
        'evening' => 'EVENING_DELIVERY',
        'pickup' => 'PICKUP_DELIVERY',
        'express' => 'EXPRESS_DELIVERY',
    ];

    foreach ($deliveryTypes as $deliveryType => $expected) {
        $deliveryOptions = new DeliveryOptions(['deliveryType' => $deliveryType]);
        $resource = new DeliveryOptionsV1Resource($deliveryOptions);
        $result = $resource->format();

        expect($result['deliveryType'])->toBe($expected);
    }
});

it('maps shipment option keys to Order API format', function () {
    $shipmentOptions = new ShipmentOptions([
        'ageCheck' => TriStateService::ENABLED,
        'signature' => TriStateService::ENABLED,
        'onlyRecipient' => TriStateService::ENABLED,
        'largeFormat' => TriStateService::ENABLED,
        'return' => TriStateService::ENABLED,
        'hideSender' => TriStateService::ENABLED,
        'labelDescription' => 'Test Label',
    ]);

    $deliveryOptions = new DeliveryOptions(['shipmentOptions' => $shipmentOptions]);
    $resource = new DeliveryOptionsV1Resource($deliveryOptions);
    $result = $resource->format();

    // Check that keys are mapped correctly
    expect($result['shipmentOptions'])
        ->toHaveKey('requiresAgeVerification')
        ->toHaveKey('requiresSignature')
        ->toHaveKey('recipientOnlyDelivery')
        ->toHaveKey('oversizedPackage')
        ->toHaveKey('printReturnLabelAtDropOff')
        ->toHaveKey('hideSender')
        ->toHaveKey('customLabelText')
        // Original camelCase keys should not be present
        ->not()->toHaveKey('ageCheck')
        ->not()->toHaveKey('signature')
        ->not()->toHaveKey('onlyRecipient')
        ->not()->toHaveKey('largeFormat')
        ->not()->toHaveKey('return')
        ->not()->toHaveKey('labelDescription');
});

it('handles tracked option with inverted no_tracking logic when disabled', function () {
    $shipmentOptions = new ShipmentOptions([
        'tracked' => TriStateService::DISABLED,
        'signature' => TriStateService::ENABLED,
    ]);

    $deliveryOptions = new DeliveryOptions(['shipmentOptions' => $shipmentOptions]);
    $resource = new DeliveryOptionsV1Resource($deliveryOptions);
    $result = $resource->format();

    // When tracked is disabled, no_tracking should be present
    expect($result['shipmentOptions'])
        ->toHaveKey('noTracking')
        ->toHaveKey('requiresSignature')
        ->not()->toHaveKey('tracked');

    expect($result['shipmentOptions']['noTracking'])->toBeInstanceOf(ArrayObject::class);
});

it('handles tracked option when enabled by not including no_tracking', function () {
    $shipmentOptions = new ShipmentOptions([
        'tracked' => TriStateService::ENABLED,
        'signature' => TriStateService::ENABLED,
    ]);

    $deliveryOptions = new DeliveryOptions(['shipmentOptions' => $shipmentOptions]);
    $resource = new DeliveryOptionsV1Resource($deliveryOptions);
    $result = $resource->format();

    // When tracked is enabled, no_tracking should NOT be present
    expect($result['shipmentOptions'])
        ->not()->toHaveKey('noTracking')
        ->not()->toHaveKey('tracked')
        ->toHaveKey('requiresSignature');
});

it('handles tracked option when not set by not including no_tracking', function () {
    $shipmentOptions = new ShipmentOptions([
        'signature' => TriStateService::ENABLED,
    ]);

    $deliveryOptions = new DeliveryOptions(['shipmentOptions' => $shipmentOptions]);
    $resource = new DeliveryOptionsV1Resource($deliveryOptions);
    $result = $resource->format();

    // When tracked is not set, no_tracking should NOT be present (tracking enabled by default)
    expect($result['shipmentOptions'])
        ->not()->toHaveKey('noTracking')
        ->not()->toHaveKey('tracked')
        ->toHaveKey('requiresSignature');
});

it('maps all supported shipment options correctly', function () {
    $shipmentOptions = new ShipmentOptions([
        'ageCheck' => TriStateService::ENABLED,
        'signature' => TriStateService::ENABLED,
        'onlyRecipient' => TriStateService::ENABLED,
        'largeFormat' => TriStateService::ENABLED,
        'return' => TriStateService::ENABLED,
        'hideSender' => TriStateService::ENABLED,
        'priorityDelivery' => TriStateService::ENABLED,
        'receiptCode' => TriStateService::ENABLED,
        'sameDayDelivery' => TriStateService::ENABLED,
        'saturdayDelivery' => TriStateService::ENABLED,
        'collect' => TriStateService::ENABLED,
    ]);

    $deliveryOptions = new DeliveryOptions(['shipmentOptions' => $shipmentOptions]);
    $resource = new DeliveryOptionsV1Resource($deliveryOptions);
    $result = $resource->format();

    expect($result['shipmentOptions'])
        ->toHaveKey('requiresAgeVerification')
        ->toHaveKey('requiresSignature')
        ->toHaveKey('recipientOnlyDelivery')
        ->toHaveKey('oversizedPackage')
        ->toHaveKey('printReturnLabelAtDropOff')
        ->toHaveKey('hideSender')
        ->toHaveKey('priorityDelivery')
        ->toHaveKey('requiresReceiptCode')
        ->toHaveKey('sameDayDelivery')
        ->toHaveKey('saturdayDelivery')
        ->toHaveKey('scheduledCollection');

    // Verify all are empty objects
    foreach ($result['shipmentOptions'] as $key => $value) {
        expect($value)->toBeInstanceOf(ArrayObject::class, "Expected {$key} to be an ArrayObject");
    }
});
