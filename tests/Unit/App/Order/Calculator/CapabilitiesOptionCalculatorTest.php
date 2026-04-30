<?php

/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\App\Order\Calculator;

use MyParcelNL\Pdk\App\Order\Calculator\General\CapabilitiesOptionCalculator;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\ShippingAddress;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\Settings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use MyParcelNL\Pdk\Tests\Bootstrap\MockMemoryCacheStorage;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\Pdk\Tests\SdkApi\MockSdkApiHandler;
use MyParcelNL\Pdk\Tests\SdkApi\Response\ExampleCapabilitiesResponse;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Tests\Uses\UsesSdkApiMock;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2;

use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\mockPdkProperty;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance(), new UsesAccountMock(), new UsesSdkApiMock());

/**
 * Build a capability result array for use with ExampleCapabilitiesResponse.
 */
function capabilityResult(string $carrier, int $contractId, array $options): array
{
    return [
        'carrier'            => $carrier,
        'contract'           => ['id' => $contractId, 'type' => 'MAIN'],
        'packageTypes'       => ['PACKAGE'],
        'options'            => $options,
        'physicalProperties' => [
            'weight' => [
                'min' => ['value' => 1, 'unit' => 'g'],
                'max' => ['value' => 23000, 'unit' => 'g'],
            ],
        ],
        'deliveryTypes'      => ['STANDARD_DELIVERY'],
        'transactionTypes'   => [],
        'collo'              => ['max' => 1],
    ];
}

/**
 * Create and calculate a PdkOrder using only the CapabilitiesOptionCalculator.
 */
function calculateOrder(string $carrier, array $shipmentOptions = []): PdkOrder
{
    $order = factory(PdkOrder::class)
        ->withShippingAddress(factory(ShippingAddress::class)->withCc('NL'))
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier($carrier)
                ->withPackageType(DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME)
                ->withDeliveryType(DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME)
                ->withShipmentOptions(
                    factory(ShipmentOptions::class)->with($shipmentOptions)
                )
        )
        ->make();

    /** @var PdkOrderOptionsServiceInterface $service */
    $service = Pdk::get(PdkOrderOptionsServiceInterface::class);

    return $service->calculate($order);
}

function resetCache(): void
{
    /** @var MockMemoryCacheStorage $storage */
    $storage = Pdk::get(StorageInterface::class);
    $storage->reset();
}

it('forces isRequired option to ENABLED', function () {
    $carrier = RefCapabilitiesSharedCarrierV2::POSTNL;

    $reset = mockPdkProperty('orderCalculators', [CapabilitiesOptionCalculator::class]);

    factory(Carrier::class)
        ->withAllCapabilities($carrier)
        ->store();

    resetCache();

    factory(Settings::class)
        ->withCarrier($carrier, [CarrierSettings::ALLOW_SIGNATURE => true])
        ->store();

    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([
        capabilityResult($carrier, 100, [
            'requiresSignature' => [
                'isRequired'          => true,
                'isSelectedByDefault' => false,
                'requires'            => [],
                'excludes'            => [],
            ],
        ]),
    ]));

    $order = calculateOrder($carrier, ['signature' => TriStateService::DISABLED]);

    expect($order->deliveryOptions->shipmentOptions->signature)->toBe(TriStateService::ENABLED);

    $reset();
});

it('applies requires: when option A is enabled and requires B, B is forced ENABLED', function () {
    $carrier = RefCapabilitiesSharedCarrierV2::POSTNL;

    $reset = mockPdkProperty('orderCalculators', [CapabilitiesOptionCalculator::class]);

    factory(Carrier::class)
        ->withAllCapabilities($carrier)
        ->store();

    resetCache();

    factory(Settings::class)
        ->withCarrier($carrier, [
            CarrierSettings::ALLOW_SIGNATURE      => true,
            CarrierSettings::ALLOW_ONLY_RECIPIENT => true,
        ])
        ->store();

    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([
        capabilityResult($carrier, 100, [
            'requiresSignature' => [
                'isRequired'          => false,
                'isSelectedByDefault' => false,
                'requires'            => ['recipientOnlyDelivery'],
                'excludes'            => [],
            ],
            'recipientOnlyDelivery' => [
                'isRequired'          => false,
                'isSelectedByDefault' => false,
                'requires'            => [],
                'excludes'            => [],
            ],
        ]),
    ]));

    $order = calculateOrder($carrier, [
        'signature'     => TriStateService::ENABLED,
        'onlyRecipient' => TriStateService::DISABLED,
    ]);

    expect($order->deliveryOptions->shipmentOptions->signature)->toBe(TriStateService::ENABLED)
        ->and($order->deliveryOptions->shipmentOptions->onlyRecipient)->toBe(TriStateService::ENABLED);

    $reset();
});

it('applies excludes: when option A is enabled and excludes B, B is forced DISABLED', function () {
    $carrier = RefCapabilitiesSharedCarrierV2::POSTNL;

    $reset = mockPdkProperty('orderCalculators', [CapabilitiesOptionCalculator::class]);

    factory(Carrier::class)
        ->withAllCapabilities($carrier)
        ->store();

    resetCache();

    factory(Settings::class)
        ->withCarrier($carrier, [
            CarrierSettings::ALLOW_SIGNATURE      => true,
            CarrierSettings::ALLOW_ONLY_RECIPIENT => true,
        ])
        ->store();

    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([
        capabilityResult($carrier, 100, [
            'requiresSignature' => [
                'isRequired'          => false,
                'isSelectedByDefault' => false,
                'requires'            => [],
                'excludes'            => ['recipientOnlyDelivery'],
            ],
            'recipientOnlyDelivery' => [
                'isRequired'          => false,
                'isSelectedByDefault' => false,
                'requires'            => [],
                'excludes'            => [],
            ],
        ]),
    ]));

    $order = calculateOrder($carrier, [
        'signature'     => TriStateService::ENABLED,
        'onlyRecipient' => TriStateService::ENABLED,
    ]);

    expect($order->deliveryOptions->shipmentOptions->signature)->toBe(TriStateService::ENABLED)
        ->and($order->deliveryOptions->shipmentOptions->onlyRecipient)->toBe(TriStateService::DISABLED);

    $reset();
});

it('forces DISABLED for options not present in capabilities response', function () {
    $carrier = RefCapabilitiesSharedCarrierV2::POSTNL;

    $reset = mockPdkProperty('orderCalculators', [CapabilitiesOptionCalculator::class]);

    factory(Carrier::class)
        ->withAllCapabilities($carrier)
        ->store();

    resetCache();

    factory(Settings::class)
        ->withCarrier($carrier, [CarrierSettings::ALLOW_SIGNATURE => true])
        ->store();

    // Capabilities response has no options at all
    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([
        capabilityResult($carrier, 100, []),
    ]));

    $order = calculateOrder($carrier, ['signature' => TriStateService::ENABLED]);

    expect($order->deliveryOptions->shipmentOptions->signature)->toBe(TriStateService::DISABLED);

    $reset();
});

it('cascades requires: A requires B, B requires C, all get enabled', function () {
    $carrier = RefCapabilitiesSharedCarrierV2::POSTNL;

    $reset = mockPdkProperty('orderCalculators', [CapabilitiesOptionCalculator::class]);

    factory(Carrier::class)
        ->withAllCapabilities($carrier)
        ->store();

    resetCache();

    factory(Settings::class)
        ->withCarrier($carrier, [
            CarrierSettings::ALLOW_SIGNATURE      => true,
            CarrierSettings::ALLOW_ONLY_RECIPIENT => true,
        ])
        ->store();

    // requiresSignature requires recipientOnlyDelivery, recipientOnlyDelivery requires requiresAgeVerification.
    // Signature is enabled, so onlyRecipient should cascade to enabled, then ageCheck should also be enabled.
    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([
        capabilityResult($carrier, 100, [
            'requiresSignature' => [
                'isRequired'          => false,
                'isSelectedByDefault' => false,
                'requires'            => ['recipientOnlyDelivery'],
                'excludes'            => [],
            ],
            'recipientOnlyDelivery' => [
                'isRequired'          => false,
                'isSelectedByDefault' => false,
                'requires'            => ['requiresAgeVerification'],
                'excludes'            => [],
            ],
            'requiresAgeVerification' => [
                'isRequired'          => false,
                'isSelectedByDefault' => false,
                'requires'            => [],
                'excludes'            => [],
            ],
        ]),
    ]));

    $order = calculateOrder($carrier, [
        'signature'     => TriStateService::ENABLED,
        'onlyRecipient' => TriStateService::DISABLED,
        'ageCheck'      => TriStateService::DISABLED,
    ]);

    expect($order->deliveryOptions->shipmentOptions->signature)->toBe(TriStateService::ENABLED)
        ->and($order->deliveryOptions->shipmentOptions->onlyRecipient)->toBe(TriStateService::ENABLED)
        ->and($order->deliveryOptions->shipmentOptions->ageCheck)->toBe(TriStateService::ENABLED);

    $reset();
});

it('sets contract ID from capabilities on delivery options', function () {
    $carrier = RefCapabilitiesSharedCarrierV2::POSTNL;

    $reset = mockPdkProperty('orderCalculators', [CapabilitiesOptionCalculator::class]);

    factory(Carrier::class)
        ->withAllCapabilities($carrier)
        ->store();

    resetCache();

    factory(Settings::class)
        ->withCarrier($carrier)
        ->store();

    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([
        capabilityResult($carrier, 42, []),
    ]));

    $order = calculateOrder($carrier);

    expect($order->deliveryOptions->contractId)->toBe(42);

    $reset();
});

it('keeps option DISABLED when carrier settings allowX is false, even if capabilities says isRequired', function () {
    $carrier = RefCapabilitiesSharedCarrierV2::POSTNL;

    $reset = mockPdkProperty('orderCalculators', [CapabilitiesOptionCalculator::class]);

    factory(Carrier::class)
        ->withAllCapabilities($carrier)
        ->store();

    resetCache();

    // Merchant explicitly disabled signature in carrier settings.
    factory(Settings::class)
        ->withCarrier($carrier, [CarrierSettings::ALLOW_SIGNATURE => false])
        ->store();

    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([
        capabilityResult($carrier, 100, [
            'requiresSignature' => [
                'isRequired'          => true,
                'isSelectedByDefault' => false,
                'requires'            => [],
                'excludes'            => [],
            ],
        ]),
    ]));

    $order = calculateOrder($carrier, ['signature' => TriStateService::ENABLED]);

    expect($order->deliveryOptions->shipmentOptions->signature)->toBe(TriStateService::DISABLED);

    $reset();
});

it('allows option when carrier settings allow it and capabilities include it', function () {
    $carrier = RefCapabilitiesSharedCarrierV2::POSTNL;

    $reset = mockPdkProperty('orderCalculators', [CapabilitiesOptionCalculator::class]);

    factory(Carrier::class)
        ->withAllCapabilities($carrier)
        ->store();

    resetCache();

    factory(Settings::class)
        ->withCarrier($carrier, [CarrierSettings::ALLOW_SIGNATURE => true])
        ->store();

    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([
        capabilityResult($carrier, 100, [
            'requiresSignature' => [
                'isRequired'          => false,
                'isSelectedByDefault' => false,
                'requires'            => [],
                'excludes'            => [],
            ],
        ]),
    ]));

    $order = calculateOrder($carrier, ['signature' => TriStateService::ENABLED]);

    // Option stays enabled — carrier settings allow it and capabilities include it.
    expect($order->deliveryOptions->shipmentOptions->signature)->toBe(TriStateService::ENABLED);

    $reset();
});
