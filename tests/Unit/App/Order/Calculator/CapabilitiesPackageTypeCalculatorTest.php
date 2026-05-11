<?php

/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\App\Order\Calculator;

use MyParcelNL\Pdk\Account\Model\Shop;
use MyParcelNL\Pdk\App\Order\Calculator\General\CapabilitiesPackageTypeCalculator;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\PdkPhysicalProperties;
use MyParcelNL\Pdk\App\Order\Model\ShippingAddress;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;
use MyParcelNL\Pdk\Settings\Model\Settings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
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

    factory(Settings::class)
        ->withCarrier($carrier)
        ->store();

    // Capabilities call for PACKAGE: carrier supports it.
    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([
        pkgCapabilityResult($carrier, ['PACKAGE']),
    ]));

    $order = factory(PdkOrder::class)
        ->withShippingAddress(factory(ShippingAddress::class)->withCc('NL'))
        ->withPhysicalProperties(factory(PdkPhysicalProperties::class)->withManualWeight(1000))
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

    factory(Settings::class)
        ->withCarrier($carrier)
        ->store();

    // The repository caches per unique (cc + package_type). The calculator iterates
    // over the carrier's declared package types only — here ['PACKAGE', 'MAILBOX'] —
    // so we expect:
    //   1. MAILBOX + NL (initial check in calculate()) → empty
    //   2. PACKAGE + NL (first fallback iteration) → carrier present
    //   - MAILBOX + NL on second fallback iteration is a cache hit, no fresh call.
    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([])); // MAILBOX
    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([
        pkgCapabilityResult($carrier, ['PACKAGE'], 1, 23000),
    ])); // PACKAGE

    $order = factory(PdkOrder::class)
        ->withShippingAddress(factory(ShippingAddress::class)->withCc('NL'))
        ->withPhysicalProperties(factory(PdkPhysicalProperties::class)->withManualWeight(1000))
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

    factory(Settings::class)
        ->withCarrier($carrier, [CarrierSettings::ALLOW_INTERNATIONAL_MAILBOX => true])
        ->store();

    // Capabilities call for MAILBOX to non-local country: carrier supports it.
    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([
        pkgCapabilityResult($carrier, ['MAILBOX'], 1, 2000),
    ]));

    $order = factory(PdkOrder::class)
        ->withShippingAddress(factory(ShippingAddress::class)->withCc('BE'))
        ->withPhysicalProperties(factory(PdkPhysicalProperties::class)->withManualWeight(1000))
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

    factory(Settings::class)
        ->withCarrier($carrier, [CarrierSettings::ALLOW_INTERNATIONAL_MAILBOX => false])
        ->store();

    // The repository caches per (cc + package_type). The fallback iteration walks the
    // carrier's declared types only — here ['PACKAGE', 'MAILBOX'] — so:
    //   1. MAILBOX + BE (initial check) → carrier present but blocked by merchant setting
    //   2. PACKAGE + BE (fallback iteration) → carrier present
    //   - MAILBOX + BE on second fallback iteration is a cache hit, no fresh call.
    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([
        pkgCapabilityResult($carrier, ['MAILBOX'], 1, 2000),
    ])); // MAILBOX
    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([
        pkgCapabilityResult($carrier, ['PACKAGE'], 1, 23000),
    ])); // PACKAGE

    $order = factory(PdkOrder::class)
        ->withShippingAddress(factory(ShippingAddress::class)->withCc('BE'))
        ->withPhysicalProperties(factory(PdkPhysicalProperties::class)->withManualWeight(1000))
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

    factory(Settings::class)
        ->withCarrier($carrier)
        ->store();

    // The fallback iteration walks the carrier's declared types only — here ['PACKAGE'].
    //   1. MAILBOX + NL (initial check) → empty
    //   2. PACKAGE + NL (only fallback iteration) → empty
    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([])); // MAILBOX
    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([])); // PACKAGE

    $order = factory(PdkOrder::class)
        ->withShippingAddress(factory(ShippingAddress::class)->withCc('NL'))
        ->withPhysicalProperties(factory(PdkPhysicalProperties::class)->withManualWeight(1000))
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

it('passes effective weight (raw + empty-weight setting) to the capability check when manualWeight is INHERIT', function () {
    // An order with totalWeight = 0 would normally fail a min-weight constraint, but the
    // empty-weight setting per package type provides the realistic export weight that the
    // shipment will eventually carry. Capability checks must use that effective value, otherwise
    // valid package types are rejected at calculator time and the user gets a surprising fallback.
    $carrier = RefCapabilitiesSharedCarrierV2::POSTNL;

    $reset = mockPdkProperty('orderCalculators', [CapabilitiesPackageTypeCalculator::class]);

    factory(Shop::class)
        ->withCarriers(
            factory(CarrierCollection::class)
                ->push(factory(Carrier::class)
                    ->withCarrier($carrier)
                    ->withCapabilityPackageTypes(['MAILBOX']))
        )
        ->store();

    factory(Settings::class)
        ->withCarrier($carrier)
        ->store();

    // Set OrderSettings AFTER Settings so the empty-weight setting isn't overwritten
    // when Settings::store() persists its (default) order sub-model.
    factory(OrderSettings::class)
        ->withEmptyMailboxWeight(2500)
        ->store();

    // MAILBOX capability requires weight in [2000, 2500] — order weight 0 alone would fail
    // min=2000, but the configured emptyMailboxWeight=2500 brings effective weight to 2500.
    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([
        pkgCapabilityResult($carrier, ['MAILBOX'], 2000, 2500),
    ]));

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

    // Mailbox stays selected — without the empty-weight fallback this would falsely fall through.
    expect($newOrder->deliveryOptions->packageType)->toBe(DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME);

    $reset();
});
