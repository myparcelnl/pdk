<?php

/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\App\Order\Calculator;

use MyParcelNL\Pdk\App\Options\Definition\IncotermsDefinition;
use MyParcelNL\Pdk\App\Order\Calculator\General\CapabilitiesDeliveryTypeCalculator;
use MyParcelNL\Pdk\App\Order\Calculator\General\IncotermsCalculator;
use MyParcelNL\Pdk\App\Order\Calculator\General\TriStateOptionCalculator;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\ShippingAddress;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Model\Settings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Tests\SdkApi\MockSdkApiHandler;
use MyParcelNL\Pdk\Tests\SdkApi\Response\ExampleCapabilitiesResponse;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Tests\Uses\UsesSdkApiMock;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\ShipmentDefsDeliveryOptionsDeliveryNameV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\ShipmentDefsIncotermsRule;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\ShipmentResponsesDeliveryOptionsPackageTypeV2;

use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\mockPdkProperty;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance(), new UsesAccountMock(), new UsesSdkApiMock());

/**
 * Build a capability result, with the given incoterms rules when $rule is not null.
 */
function incotermsCapabilityResult(string $carrier, ?array $rule): array
{
    $result = [
        'carrier'            => $carrier,
        'contract'           => ['id' => 100, 'type' => 'MAIN'],
        'packageTypes'       => ['PACKAGE'],
        'options'            => (object) [],
        'physicalProperties' => [
            'weight' => [
                'min' => ['value' => 0, 'unit' => 'g'],
                'max' => ['value' => 30000, 'unit' => 'g'],
            ],
        ],
        'deliveryTypes'      => ['STANDARD_DELIVERY'],
        'transactionTypes'   => [],
        'collo'              => ['max' => 1],
    ];

    if (null !== $rule) {
        $result['incoterms'] = ['rule' => $rule];
    }

    return $result;
}

it('gives the order a rule the capabilities allow', function (
    array $results,
    $orderRule,
    $carrierRule,
    $expected
) {
    $carrier = RefCapabilitiesSharedCarrierV2::POSTNL;
    $reset   = mockPdkProperty('orderCalculators', [TriStateOptionCalculator::class, IncotermsCalculator::class]);

    factory(Carrier::class)
        ->withAllCapabilities($carrier)
        ->store();

    factory(Settings::class)
        ->withCarrier($carrier, [(new IncotermsDefinition())->getCarrierSettingsKey() => $carrierRule])
        ->store();

    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse($results));

    $order = factory(PdkOrder::class)
        ->withShippingAddress(factory(ShippingAddress::class)->withCc('US'))
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier($carrier)
                ->withPackageType(ShipmentResponsesDeliveryOptionsPackageTypeV2::PACKAGE)
                ->withDeliveryType(ShipmentDefsDeliveryOptionsDeliveryNameV2::STANDARD)
                ->withShipmentOptions(factory(ShipmentOptions::class)->with(['incoterms' => $orderRule]))
        )
        ->make();

    /** @var PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsServiceInterface::class);
    $newOrder = $service->calculate($order);

    expect($newOrder->deliveryOptions->shipmentOptions->incoterms)->toBe($expected);

    $reset();
})->with([
    'order rule is allowed' => [
        [incotermsCapabilityResult(RefCapabilitiesSharedCarrierV2::POSTNL, ['default' => ShipmentDefsIncotermsRule::DAP, 'values' => [ShipmentDefsIncotermsRule::DAP, ShipmentDefsIncotermsRule::DDP]])],
        ShipmentDefsIncotermsRule::DDP,
        TriStateService::INHERIT,
        ShipmentDefsIncotermsRule::DDP,
    ],
    'order rule is not allowed' => [
        [incotermsCapabilityResult(RefCapabilitiesSharedCarrierV2::POSTNL, ['default' => ShipmentDefsIncotermsRule::DAP, 'values' => [ShipmentDefsIncotermsRule::DAP]])],
        ShipmentDefsIncotermsRule::DDP,
        TriStateService::INHERIT,
        ShipmentDefsIncotermsRule::DAP,
    ],
    'no rule chosen' => [
        [incotermsCapabilityResult(RefCapabilitiesSharedCarrierV2::POSTNL, ['default' => ShipmentDefsIncotermsRule::DAP, 'values' => [ShipmentDefsIncotermsRule::DAP, ShipmentDefsIncotermsRule::DDP]])],
        TriStateService::INHERIT,
        TriStateService::INHERIT,
        ShipmentDefsIncotermsRule::DAP,
    ],
    'carrier rule is allowed' => [
        [incotermsCapabilityResult(RefCapabilitiesSharedCarrierV2::POSTNL, ['default' => ShipmentDefsIncotermsRule::DAP, 'values' => [ShipmentDefsIncotermsRule::DAP, ShipmentDefsIncotermsRule::DDP]])],
        TriStateService::INHERIT,
        ShipmentDefsIncotermsRule::DDP,
        ShipmentDefsIncotermsRule::DDP,
    ],
    'carrier rule is not allowed' => [
        [incotermsCapabilityResult(RefCapabilitiesSharedCarrierV2::POSTNL, ['default' => ShipmentDefsIncotermsRule::DAP, 'values' => [ShipmentDefsIncotermsRule::DAP]])],
        TriStateService::INHERIT,
        ShipmentDefsIncotermsRule::DDP,
        ShipmentDefsIncotermsRule::DAP,
    ],
    'capability without incoterms' => [
        [incotermsCapabilityResult(RefCapabilitiesSharedCarrierV2::POSTNL, null)],
        ShipmentDefsIncotermsRule::DDP,
        TriStateService::INHERIT,
        TriStateService::DISABLED,
    ],
    'no capability' => [
        [],
        ShipmentDefsIncotermsRule::DDP,
        TriStateService::INHERIT,
        TriStateService::DISABLED,
    ],
]);

it('runs after the tri-state and delivery type calculators', function () {
    $calculators = Pdk::get('orderCalculators');
    $position    = array_search(IncotermsCalculator::class, $calculators, true);

    expect($position)->toBeGreaterThan(array_search(TriStateOptionCalculator::class, $calculators, true))
        ->and($position)->toBeGreaterThan(array_search(CapabilitiesDeliveryTypeCalculator::class, $calculators, true));
});

it('gives an order without a country code no rule', function () {
    $carrier = RefCapabilitiesSharedCarrierV2::POSTNL;
    $reset   = mockPdkProperty('orderCalculators', [IncotermsCalculator::class]);

    factory(Carrier::class)
        ->withAllCapabilities($carrier)
        ->store();

    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([
        incotermsCapabilityResult($carrier, ['default' => ShipmentDefsIncotermsRule::DAP, 'values' => [ShipmentDefsIncotermsRule::DAP]]),
    ]));

    $order = factory(PdkOrder::class)
        ->withShippingAddress(factory(ShippingAddress::class)->withCc(null))
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier($carrier)
                ->withPackageType(ShipmentResponsesDeliveryOptionsPackageTypeV2::PACKAGE)
        )
        ->make();

    /** @var PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsServiceInterface::class);
    $newOrder = $service->calculate($order);

    expect($newOrder->deliveryOptions->shipmentOptions->incoterms)->toBe(TriStateService::DISABLED);

    $reset();
});
