<?php

/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\App\Order\Calculator;

use MyParcelNL\Pdk\Account\Model\Shop;
use MyParcelNL\Pdk\App\Order\Calculator\General\CapabilitiesPackageTypeCalculator;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\ShippingAddress;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\Settings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use MyParcelNL\Pdk\Tests\Bootstrap\MockMemoryCacheStorage;
use MyParcelNL\Pdk\Tests\SdkApi\MockSdkApiHandler;
use MyParcelNL\Pdk\Tests\SdkApi\Response\ExampleCapabilitiesResponse;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Tests\Uses\UsesSdkApiMock;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2;

use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\mockPdkProperty;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance(), new UsesAccountMock(), new UsesSdkApiMock());

/**
 * Build a single capabilities result entry for the mock API response.
 */
function pkgCapabilityResult(
    string $carrier,
    array $packageTypes,
    int $weightMin = 1,
    int $weightMax = 23000
): array {
    return [
        'carrier'            => $carrier,
        'contract'           => ['id' => 100, 'type' => 'MAIN'],
        'packageTypes'       => $packageTypes,
        'options'            => (object) [],
        'physicalProperties' => [
            'weight' => [
                'min' => ['value' => $weightMin, 'unit' => 'g'],
                'max' => ['value' => $weightMax, 'unit' => 'g'],
            ],
        ],
        'deliveryTypes'      => ['STANDARD_DELIVERY'],
        'transactionTypes'   => [],
        'collo'              => ['max' => 1],
    ];
}

function resetStorageCache(): void
{
    /** @var MockMemoryCacheStorage $storage */
    $storage = Pdk::get(StorageInterface::class);
    $storage->reset();
}

it('keeps package type when it is available in capabilities', function () {
    $carrier = RefCapabilitiesSharedCarrierV2::POSTNL;

    $reset = mockPdkProperty('orderCalculators', [CapabilitiesPackageTypeCalculator::class]);

    factory(Shop::class)
        ->withCarriers(
            factory(CarrierCollection::class)
                ->push(factory(Carrier::class)
                    ->withCarrier($carrier)
                    ->withCapabilityPackageTypes(['PACKAGE', 'MAILBOX']))
        )
        ->store();

    resetStorageCache();

    factory(Settings::class)
        ->withCarrier($carrier)
        ->store();

    // Capabilities call for PACKAGE: carrier supports it.
    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([
        pkgCapabilityResult($carrier, ['PACKAGE']),
    ]));

    $order = factory(PdkOrder::class)
        ->withShippingAddress(factory(ShippingAddress::class)->withCc('NL'))
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier($carrier)
                ->withPackageType(DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME)
        )
        ->make();

    /** @var PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsServiceInterface::class);
    $newOrder = $service->calculate($order);

    expect($newOrder->deliveryOptions->packageType)->toBe(DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME);

    $reset();
});

it('falls back to next available type when selected type is not in capabilities', function () {
    $carrier = RefCapabilitiesSharedCarrierV2::POSTNL;

    $reset = mockPdkProperty('orderCalculators', [CapabilitiesPackageTypeCalculator::class]);

    factory(Shop::class)
        ->withCarriers(
            factory(CarrierCollection::class)
                ->push(factory(Carrier::class)
                    ->withCarrier($carrier)
                    ->withCapabilityPackageTypes(['PACKAGE', 'MAILBOX']))
        )
        ->store();

    resetStorageCache();

    factory(Settings::class)
        ->withCarrier($carrier)
        ->store();

    // Repository caches per unique (cc + package_type), so 5 total API calls:
    // 1. MAILBOX + NL (initial check) → empty
    // 2. PACKAGE + NL (from getPackageTypeWeights) → carrier present
    // 3. UNFRANKED + NL → empty
    // 4. DIGITAL_STAMP + NL → empty
    // 5. SMALL_PACKAGE + NL → empty
    // All subsequent lookups are cached.

    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([]));

    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([
        pkgCapabilityResult($carrier, ['PACKAGE'], 1, 23000),
    ]));

    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([]));
    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([]));
    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([]));

    $order = factory(PdkOrder::class)
        ->withShippingAddress(factory(ShippingAddress::class)->withCc('NL'))
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier($carrier)
                ->withPackageType(DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME)
        )
        ->make();

    /** @var PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsServiceInterface::class);
    $newOrder = $service->calculate($order);

    expect($newOrder->deliveryOptions->packageType)->toBe(DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME);

    $reset();
});

it('keeps international mailbox when allowInternationalMailbox is enabled', function () {
    $carrier = RefCapabilitiesSharedCarrierV2::POSTNL;

    $reset = mockPdkProperty('orderCalculators', [CapabilitiesPackageTypeCalculator::class]);

    factory(Shop::class)
        ->withCarriers(
            factory(CarrierCollection::class)
                ->push(factory(Carrier::class)
                    ->withCarrier($carrier)
                    ->withCapabilityPackageTypes(['PACKAGE', 'MAILBOX']))
        )
        ->store();

    resetStorageCache();

    factory(Settings::class)
        ->withCarrier($carrier, [CarrierSettings::ALLOW_INTERNATIONAL_MAILBOX => true])
        ->store();

    // Capabilities call for MAILBOX to non-local country: carrier supports it.
    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([
        pkgCapabilityResult($carrier, ['MAILBOX'], 1, 2000),
    ]));

    $order = factory(PdkOrder::class)
        ->withShippingAddress(factory(ShippingAddress::class)->withCc('BE'))
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier($carrier)
                ->withPackageType(DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME)
        )
        ->make();

    /** @var PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsServiceInterface::class);
    $newOrder = $service->calculate($order);

    expect($newOrder->deliveryOptions->packageType)->toBe(DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME);

    $reset();
});

it('falls back when international mailbox is blocked by merchant setting', function () {
    $carrier = RefCapabilitiesSharedCarrierV2::POSTNL;

    $reset = mockPdkProperty('orderCalculators', [CapabilitiesPackageTypeCalculator::class]);

    factory(Shop::class)
        ->withCarriers(
            factory(CarrierCollection::class)
                ->push(factory(Carrier::class)
                    ->withCarrier($carrier)
                    ->withCapabilityPackageTypes(['PACKAGE', 'MAILBOX']))
        )
        ->store();

    resetStorageCache();

    factory(Settings::class)
        ->withCarrier($carrier, [CarrierSettings::ALLOW_INTERNATIONAL_MAILBOX => false])
        ->store();

    // The repository caches responses per unique args, so each unique
    // (cc + package_type) combination only triggers one API call.
    //
    // Call sequence:
    // 1. Initial check: MAILBOX + BE → API call (cached)
    // 2. getPackageTypeWeights iterates all 5 types:
    //    - PACKAGE + BE → API call (new)
    //    - MAILBOX + BE → cached from (1)
    //    - UNFRANKED + BE → API call (new)
    //    - DIGITAL_STAMP + BE → API call (new)
    //    - SMALL_PACKAGE + BE → API call (new)
    // 3. Carrier availability check: all cached from (1)+(2)
    //
    // Total: 5 API calls in order: MAILBOX, PACKAGE, UNFRANKED, DIGITAL_STAMP, SMALL_PACKAGE.

    // 1. MAILBOX — initial check (carrier present but blocked by setting)
    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([
        pkgCapabilityResult($carrier, ['MAILBOX'], 1, 2000),
    ]));

    // 2. PACKAGE — from getPackageTypeWeights (carrier present)
    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([
        pkgCapabilityResult($carrier, ['PACKAGE'], 1, 23000),
    ]));

    // 3. UNFRANKED (letter) — empty
    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([]));

    // 4. DIGITAL_STAMP — empty
    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([]));

    // 5. SMALL_PACKAGE — empty
    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([]));

    $order = factory(PdkOrder::class)
        ->withShippingAddress(factory(ShippingAddress::class)->withCc('BE'))
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier($carrier)
                ->withPackageType(DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME)
        )
        ->make();

    /** @var PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsServiceInterface::class);
    $newOrder = $service->calculate($order);

    // Mailbox blocked for international → skipped in fallback, falls back to package.
    expect($newOrder->deliveryOptions->packageType)->toBe(DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME);

    $reset();
});

it('falls back to default when no capabilities match at all', function () {
    $carrier = RefCapabilitiesSharedCarrierV2::POSTNL;

    $reset = mockPdkProperty('orderCalculators', [CapabilitiesPackageTypeCalculator::class]);

    factory(Shop::class)
        ->withCarriers(
            factory(CarrierCollection::class)
                ->push(factory(Carrier::class)
                    ->withCarrier($carrier)
                    ->withCapabilityPackageTypes(['PACKAGE']))
        )
        ->store();

    resetStorageCache();

    factory(Settings::class)
        ->withCarrier($carrier)
        ->store();

    // Repository caches per unique (cc + package_type), so 5 total API calls:
    // 1. MAILBOX + NL (initial check) → empty
    // 2. PACKAGE + NL → empty
    // 3. UNFRANKED + NL → empty
    // 4. DIGITAL_STAMP + NL → empty
    // 5. SMALL_PACKAGE + NL → empty
    // All subsequent lookups are cached.

    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([]));
    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([]));
    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([]));
    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([]));
    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([]));

    $order = factory(PdkOrder::class)
        ->withShippingAddress(factory(ShippingAddress::class)->withCc('NL'))
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier($carrier)
                ->withPackageType(DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME)
        )
        ->make();

    /** @var PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsServiceInterface::class);
    $newOrder = $service->calculate($order);

    expect($newOrder->deliveryOptions->packageType)->toBe(DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME);

    $reset();
});
