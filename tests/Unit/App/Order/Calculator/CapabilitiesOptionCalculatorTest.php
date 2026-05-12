<?php

/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\App\Order\Calculator;

use MyParcelNL\Pdk\App\Options\Definition\AbstractOrderOptionDefinition;
use MyParcelNL\Pdk\App\Options\Definition\OnlyRecipientDefinition;
use MyParcelNL\Pdk\App\Options\Definition\SignatureDefinition;
use MyParcelNL\Pdk\App\Order\Calculator\General\CapabilitiesOptionCalculator;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\ShippingAddress;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Logger\Contract\PdkLoggerInterface;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\Settings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\Pdk\Tests\SdkApi\MockSdkApiHandler;
use MyParcelNL\Pdk\Tests\SdkApi\Response\ExampleCapabilitiesResponse;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockEachLogger;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Tests\Uses\UsesSdkApiMock;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2;
use Psr\Log\LogLevel;

use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\mockPdkProperty;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance(), new UsesAccountMock(), new UsesSdkApiMock(), new UsesMockEachLogger());

final class InvalidCapabilityKeyDefinition extends AbstractOrderOptionDefinition
{
    public function getShipmentOptionsKey(): ?string
    {
        return 'signature';
    }

    public function getCapabilitiesOptionsKey(): ?string
    {
        return 'nonExistentCapabilityKey';
    }
}

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

it('forces isRequired option to ENABLED', function () {
    $carrier = RefCapabilitiesSharedCarrierV2::POSTNL;

    $reset = mockPdkProperty('orderCalculators', [CapabilitiesOptionCalculator::class]);

    factory(Carrier::class)
        ->withAllCapabilities($carrier)
        ->store();

    factory(Settings::class)
        ->withCarrier($carrier, [(new SignatureDefinition())->getAllowSettingsKey() => true])
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

    factory(Settings::class)
        ->withCarrier($carrier, [
            (new SignatureDefinition())->getAllowSettingsKey()      => true,
            (new OnlyRecipientDefinition())->getAllowSettingsKey() => true,
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

    factory(Settings::class)
        ->withCarrier($carrier, [
            (new SignatureDefinition())->getAllowSettingsKey()      => true,
            (new OnlyRecipientDefinition())->getAllowSettingsKey() => true,
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

    factory(Settings::class)
        ->withCarrier($carrier, [(new SignatureDefinition())->getAllowSettingsKey() => true])
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

    factory(Settings::class)
        ->withCarrier($carrier, [
            (new SignatureDefinition())->getAllowSettingsKey()      => true,
            (new OnlyRecipientDefinition())->getAllowSettingsKey() => true,
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

it('forces option ENABLED when capabilities says isRequired, even if merchant allowX is false', function () {
    // Merchant `allow*` flags only affect checkout display (DeliveryOptionsService);
    // at order-processing time capabilities have final say so the order remains exportable.
    $carrier = RefCapabilitiesSharedCarrierV2::POSTNL;

    $reset = mockPdkProperty('orderCalculators', [CapabilitiesOptionCalculator::class]);

    factory(Carrier::class)
        ->withAllCapabilities($carrier)
        ->store();

    factory(Settings::class)
        ->withCarrier($carrier, [(new SignatureDefinition())->getAllowSettingsKey() => false])
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

it('allows option when carrier settings allow it and capabilities include it', function () {
    $carrier = RefCapabilitiesSharedCarrierV2::POSTNL;

    $reset = mockPdkProperty('orderCalculators', [CapabilitiesOptionCalculator::class]);

    factory(Carrier::class)
        ->withAllCapabilities($carrier)
        ->store();

    factory(Settings::class)
        ->withCarrier($carrier, [(new SignatureDefinition())->getAllowSettingsKey() => true])
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

it('disables every shipment option when the carrier capability is missing for the combination', function () {
    $carrier = RefCapabilitiesSharedCarrierV2::POSTNL;

    $reset = mockPdkProperty('orderCalculators', [CapabilitiesOptionCalculator::class]);

    factory(Carrier::class)
        ->withAllCapabilities($carrier)
        ->store();


    // Permissive carrier settings — but capabilities will say "no support" anyway.
    factory(Settings::class)
        ->withCarrier($carrier, [
            (new SignatureDefinition())->getAllowSettingsKey()      => true,
            (new OnlyRecipientDefinition())->getAllowSettingsKey() => true,
        ])
        ->store();

    // Empty capabilities response — carrier doesn't support this carrier+package_type+delivery_type.
    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([]));

    $order = calculateOrder($carrier, [
        'signature'     => TriStateService::ENABLED,
        'onlyRecipient' => TriStateService::ENABLED,
        'ageCheck'      => TriStateService::ENABLED,
    ]);

    expect($order->deliveryOptions->shipmentOptions->signature)->toBe(TriStateService::DISABLED)
        ->and($order->deliveryOptions->shipmentOptions->onlyRecipient)->toBe(TriStateService::DISABLED)
        ->and($order->deliveryOptions->shipmentOptions->ageCheck)->toBe(TriStateService::DISABLED);

    $reset();
});

it('logs a warning when a definition references a capabilities key that has no SDK getter', function () {
    $carrier = RefCapabilitiesSharedCarrierV2::POSTNL;

    $resetCalculators = mockPdkProperty('orderCalculators', [CapabilitiesOptionCalculator::class]);
    $resetDefinitions = mockPdkProperty('orderOptionDefinitions', [new InvalidCapabilityKeyDefinition()]);

    factory(Carrier::class)
        ->withAllCapabilities($carrier)
        ->store();

    factory(Settings::class)
        ->withCarrier($carrier, [(new SignatureDefinition())->getAllowSettingsKey() => true])
        ->store();

    // Capabilities response with at least one option present, so $capability->getOptions()
    // returns a non-null object and getCapabilityOption() is reached.
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

    calculateOrder($carrier, ['signature' => TriStateService::ENABLED]);

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockLogger $logger */
    $logger      = Pdk::get(PdkLoggerInterface::class);
    $warningLogs = $logger->getLogs(LogLevel::WARNING);

    $matching = array_values(array_filter($warningLogs, static function (array $log) {
        return strpos($log['message'], 'nonExistentCapabilityKey') !== false;
    }));

    expect($matching)->toHaveCount(1)
        ->and($matching[0]['context']['capabilitiesKey'])->toBe('nonExistentCapabilityKey')
        ->and($matching[0]['context']['expectedGetter'])->toBe('getNonExistentCapabilityKey');

    $resetDefinitions();
    $resetCalculators();
});
