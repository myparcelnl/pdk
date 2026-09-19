<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Unit\App\Endpoint\Resource;

use ArrayObject;
use MyParcelNL\Pdk\App\Endpoint\Resource\DeliveryOptionsV1Resource;
use MyParcelNL\Pdk\Carrier\Contract\CarrierRepositoryInterface;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\RetailLocation;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefTypesDeliveryTypeV2;
use MyParcelNL\Sdk\Client\Generated\OrderApi\Model\DeliveryType as OrderApiDeliveryType;
use MyParcelNL\Sdk\Client\Generated\OrderApi\Model\PackageType as OrderApiPackageType;

use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance(), new UsesAccountMock());

it('formats delivery options correctly', function () {
    $shipmentOptions = new ShipmentOptions([
        'signature' => TriStateService::ENABLED,
        'onlyRecipient' => TriStateService::ENABLED,
        'return' => TriStateService::DISABLED,
        'largeFormat' => TriStateService::ENABLED,
        'ageCheck' => TriStateService::ENABLED,
        'insurance' => 50000,
    ]);

    $carrier = factory(Carrier::class)->withCarrier('POSTNL')->make();

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

    // Insurance: 50000 cents (€500) → 500_000_000 micros (€500 × 1_000_000)
    expect($result['shipmentOptions']['insurance'])
        ->toBeArray()
        ->toHaveKey('amount', 50000 * 10_000);
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
        'insurance' => 10000, // 10000 cents = €100
    ]);

    $deliveryOptions = new DeliveryOptions([
        'shipmentOptions' => $shipmentOptions,
    ]);

    $resource = new DeliveryOptionsV1Resource($deliveryOptions);
    $result = $resource->format();

    // 10000 cents (€100) → 100_000_000 micros (€100 × 1_000_000)
    expect($result['shipmentOptions'])
        ->toHaveKey('insurance')
        ->and($result['shipmentOptions']['insurance'])
        ->toBeArray()
        ->toHaveKey('amount', 10000 * 10_000);
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

it('formats future dates as ISO 8601 string', function () {
    // Set a relative date one day in the future, as currently the PDK will return `null` for past dates.
    $deliveryOptions = new DeliveryOptions([
        'date' => new \DateTime('+1 day')
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
    $carrier = Pdk::get(CarrierRepositoryInterface::class)->find('POSTNL');
    $carrier->deliveryTypes = [RefTypesDeliveryTypeV2::PICKUP];
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
        $carrier = factory(Carrier::class)->withCarrier($carrierName)->make();
        $deliveryOptions = new DeliveryOptions(['carrier' => $carrier]);
        $resource = new DeliveryOptionsV1Resource($deliveryOptions);
        $result = $resource->format();

        expect($result['carrier'])->toBe($expected);
    }
});

it('formats all complete SDK type mappings as valid Order API values', function (string $attribute, int $id, string $name, string $v2Name) {
    $options = new DeliveryOptions([$attribute => $name]);
    $result  = (new DeliveryOptionsV1Resource($options))->format();
    $allowed = $attribute === 'packageType'
        ? OrderApiPackageType::getAllowableEnumValues()
        : OrderApiDeliveryType::getAllowableEnumValues();

    $expected = in_array($v2Name, $allowed, true) ? $v2Name : null;

    expect($result[$attribute])->toBe($expected);
})->with('apiTypeMappings');

it('preserves valid Order API types outside the Core API map', function (string $attribute) {
    $allowedValues = $attribute === 'packageType'
        ? OrderApiPackageType::getAllowableEnumValues()
        : OrderApiDeliveryType::getAllowableEnumValues();

    foreach ($allowedValues as $v2Name) {
        $options = new DeliveryOptions();
        $options->{$attribute} = $v2Name;

        expect((new DeliveryOptionsV1Resource($options))->format()[$attribute])->toBe($v2Name);
    }
})->with(['packageType', 'deliveryType']);

it('keeps the Order API normalization for delivery names without a Core API mapping', function () {
    $options = new DeliveryOptions();
    $options->deliveryType = 'pickup_express';

    expect((new DeliveryOptionsV1Resource($options))->format()['deliveryType'])
        ->toBe(OrderApiDeliveryType::PICKUP_EXPRESS_DELIVERY);
});

it('does not send unknown package or delivery types to the Order API', function () {
    $options = new DeliveryOptions();
    $options->packageType = 'unknown_package';
    $options->deliveryType = 'unknown_delivery';
    $result = (new DeliveryOptionsV1Resource($options))->format();

    expect($result['packageType'])->toBeNull()
        ->and($result['deliveryType'])->toBeNull();
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

it('never sends the retired tracked option', function () {
    $options = formatWithNoTracking(TriStateService::ENABLED);

    expect($options)->not->toHaveKey('tracked');
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
        'freshFood' => TriStateService::ENABLED,
        'frozen' => TriStateService::ENABLED,
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
        ->toHaveKey('scheduledCollection')
        ->toHaveKey('freshFood')
        ->toHaveKey('frozen');

    // Verify all are empty objects
    foreach ($result['shipmentOptions'] as $key => $value) {
        expect($value)->toBeInstanceOf(ArrayObject::class, "Expected {$key} to be an ArrayObject");
    }
});

/**
 * Tracking is the default wherever the carrier supports it, so noTracking is only ever sent as an
 * explicit opt-out. Getting the direction of this wrong would silently disable tracking for every
 * merchant, hence a case per tri-state value.
 */
function formatWithNoTracking(int $noTracking): array
{
    $deliveryOptions = new DeliveryOptions([
        'carrier'         => factory(Carrier::class)->withCarrier('POSTNL')->make(),
        'packageType'     => 'package',
        'deliveryType'    => 'standard',
        'shipmentOptions' => new ShipmentOptions(['noTracking' => $noTracking]),
    ]);

    return (new DeliveryOptionsV1Resource($deliveryOptions))->format()['shipmentOptions'];
}

it('omits no tracking when the option is not set', function () {
    expect(formatWithNoTracking(TriStateService::INHERIT))->not->toHaveKey('noTracking');
});

it('omits no tracking when the merchant wants tracking', function () {
    expect(formatWithNoTracking(TriStateService::DISABLED))->not->toHaveKey('noTracking');
});

it('sends an empty object when the merchant opts out of tracking', function () {
    $options = formatWithNoTracking(TriStateService::ENABLED);

    expect($options)->toHaveKey('noTracking')
        ->and($options['noTracking'])->toBeInstanceOf(ArrayObject::class);
});
