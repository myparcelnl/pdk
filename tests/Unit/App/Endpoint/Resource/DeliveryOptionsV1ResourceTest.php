<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Unit\App\Endpoint\Resource;

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
        ->toContain('SIGNATURE')
        ->toContain('ONLY_RECIPIENT')
        ->toContain('LARGE_FORMAT')
        ->toContain('AGE_CHECK')
        ->toContain('INSURANCE')
        ->not()->toContain('RETURN');
});

it('returns empty array when no shipment options are enabled', function () {
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

it('returns null for carrier when no carrier name is given', function () {
    $deliveryOptions = new DeliveryOptions([
        'carrier' => new Carrier(['name' => null]),
    ]);

    $resource = new DeliveryOptionsV1Resource($deliveryOptions);
    $result = $resource->format();

    expect($result['carrier'])->toBeNull();
});

it('returns null for packageType when no package type is given', function () {
    $deliveryOptions = new DeliveryOptions([
        'packageType' => null,
    ]);

    $resource = new DeliveryOptionsV1Resource($deliveryOptions);
    $result = $resource->format();

    expect($result['packageType'])->toBeNull();
});

it('returns null for deliveryType when no delivery type is given', function () {
    $deliveryOptions = new DeliveryOptions([
        'deliveryType' => null,
    ]);

    $resource = new DeliveryOptionsV1Resource($deliveryOptions);
    $result = $resource->format();

    expect($result['deliveryType'])->toBeNull();
});
