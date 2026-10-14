<?php

/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\App\Order\Calculator;

use MyParcelNL\Pdk\App\Options\Definition\AbstractOrderOptionDefinition;
use MyParcelNL\Pdk\App\Options\Definition\InsuranceDefinition;
use MyParcelNL\Pdk\App\Options\Definition\OnlyRecipientDefinition;
use MyParcelNL\Pdk\App\Options\Definition\SignatureDefinition;
use MyParcelNL\Pdk\App\Order\Calculator\General\CapabilitiesOptionCalculator;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\PdkOrderLine;
use MyParcelNL\Pdk\App\Order\Model\PdkProduct;
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

use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\ShipmentResponsesDeliveryOptionsPackageTypeV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\ShipmentDefsDeliveryOptionsDeliveryNameV2;
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
                ->withPackageType(ShipmentResponsesDeliveryOptionsPackageTypeV2::PACKAGE)
                ->withDeliveryType(ShipmentDefsDeliveryOptionsDeliveryNameV2::STANDARD)
                ->withShipmentOptions(
                    factory(ShipmentOptions::class)->with($shipmentOptions)
                )
        )
        ->make();

    /** @var PdkOrderOptionsServiceInterface $service */
    $service = Pdk::get(PdkOrderOptionsServiceInterface::class);

    return $service->calculate($order);
}

it('sends isBusiness on the capabilities request based on the shipping address company', function (?string $company, bool $expected) {
    $carrier = RefCapabilitiesSharedCarrierV2::POSTNL;

    $reset = mockPdkProperty('orderCalculators', [CapabilitiesOptionCalculator::class]);

    factory(Carrier::class)
        ->withAllCapabilities($carrier)
        ->store();

    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([
        capabilityResult($carrier, 100, []),
    ]));

    $order = factory(PdkOrder::class)
        ->withShippingAddress(factory(ShippingAddress::class)->withCc('NL')->with(['company' => $company]))
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier($carrier)
                ->withPackageType(ShipmentResponsesDeliveryOptionsPackageTypeV2::PACKAGE)
                ->withDeliveryType(ShipmentDefsDeliveryOptionsDeliveryNameV2::STANDARD)
        )
        ->make();

    Pdk::get(PdkOrderOptionsServiceInterface::class)->calculate($order);

    $body = json_decode((string) MockSdkApiHandler::getHandler()->getLastRequest()->getBody(), true);

    expect($body['recipient'])->toHaveKey('isBusiness', $expected);

    $reset();
})->with([
    'business (company entered)' => ['Acme B.V.', true],
    'consumer (no company)'      => [null, false],
]);

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

it('applies what insurance requires only when the calculated insured amount is above zero', function (
    int $orderPrice,
    int $orderInsurance,
    int $expectedInsurance,
    int $expectedRequiredOption
) {
    $carrier = RefCapabilitiesSharedCarrierV2::POSTNL;

    factory(Carrier::class)
        ->withAllCapabilities($carrier)
        ->store();

    // Insure from €500 up to €500. Signature and only recipient are off in the settings.
    factory(Settings::class)
        ->withCarrier($carrier, [
            (new InsuranceDefinition())->getCarrierSettingsKey()     => true,
            (new SignatureDefinition())->getCarrierSettingsKey()     => false,
            (new OnlyRecipientDefinition())->getCarrierSettingsKey() => false,
            CarrierSettings::EXPORT_INSURANCE_FROM_AMOUNT            => 500,
            CarrierSettings::EXPORT_INSURANCE_PRICE_PERCENTAGE       => 100,
            CarrierSettings::EXPORT_INSURANCE_UP_TO                  => 50000,
            CarrierSettings::EXPORT_INSURANCE_UP_TO_UNIQUE           => 50000,
        ])
        ->store();

    $option = [
        'isRequired'          => false,
        'isSelectedByDefault' => false,
        'requires'            => [],
        'excludes'            => [],
    ];

    $capabilities = new ExampleCapabilitiesResponse([
        capabilityResult($carrier, 100, [
            'requiresSignature'     => $option,
            'recipientOnlyDelivery' => $option,
            'insurance'             => array_merge($option, [
                'requires' => ['requiresSignature', 'recipientOnlyDelivery'],
                'default'  => ['currency' => 'EUR', 'amount' => 0],
                'min'      => ['currency' => 'EUR', 'amount' => 0],
                'max'      => ['currency' => 'EUR', 'amount' => 500000],
            ]),
        ]),
    ]);

    // One response per capabilities request in the calculator chain. Unused responses are reset after the test.
    MockSdkApiHandler::enqueue(...array_fill(0, 6, $capabilities));

    factory(PdkProduct::class)
        ->withExternalIdentifier('insured-product')
        ->withPrice($orderPrice)
        ->store();

    $order = factory(PdkOrder::class)
        ->withShippingAddress(factory(ShippingAddress::class)->withCc('NL'))
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier($carrier)
                ->withPackageType(ShipmentResponsesDeliveryOptionsPackageTypeV2::PACKAGE)
                ->withDeliveryType(ShipmentDefsDeliveryOptionsDeliveryNameV2::STANDARD)
                ->withShipmentOptions(factory(ShipmentOptions::class)->with(['insurance' => $orderInsurance]))
        )
        ->withLines([
            factory(PdkOrderLine::class)
                ->withProduct('insured-product')
                ->withPrice($orderPrice),
        ])
        ->make();

    $shipmentOptions = Pdk::get(PdkOrderOptionsServiceInterface::class)
        ->calculate($order)
        ->deliveryOptions
        ->shipmentOptions;

    expect($shipmentOptions->insurance)->toBe($expectedInsurance)
        ->and($shipmentOptions->signature)->toBe($expectedRequiredOption)
        ->and($shipmentOptions->onlyRecipient)->toBe($expectedRequiredOption);
})->with([
    'order below the insure-from amount'   => [10000, TriStateService::INHERIT, 0, TriStateService::DISABLED],
    'order above the insure-from amount'   => [60000, TriStateService::INHERIT, 50000, TriStateService::ENABLED],
    'insured amount entered on the order' => [10000, 50000, 50000, TriStateService::ENABLED],
]);
