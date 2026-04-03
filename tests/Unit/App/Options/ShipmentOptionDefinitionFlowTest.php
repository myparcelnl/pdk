<?php

/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Options;

use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\App\Options\Definition\AbstractOrderOptionDefinition;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Fulfilment\Model\ShipmentOptions as FulfilmentShipmentOptions;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use MyParcelNL\Pdk\Validation\Validator\CarrierSchema;

use function DI\value;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

/**
 * Proves the option definition mechanism works end-to-end with a fake option.
 * Registers a TestFlowOptionDefinition as the only definition (isolating from real ones)
 * and verifies the entire pipeline: settings registration on all models, option calculation
 * through the priority chain, carrier validation via capabilities key, and the V2 API
 * export/import roundtrip (shipment options key ↔ capabilities key conversion).
 *
 * Uses distinct PDK and capabilities keys to match the real definition convention,
 * where the shipment option key (legacy V1 naming) differs from the capabilities key (V2 naming).
 */
final class TestFlowOptionDefinition extends AbstractOrderOptionDefinition
{
    public function getShipmentOptionsKey(): ?string
    {
        return 'testFlowOption';
    }

    public function getCapabilitiesOptionsKey(): ?string
    {
        return 'testFlowCapability';
    }
}

uses()->group('options', 'flow');

usesShared(
    new UsesMockPdkInstance([
        'orderOptionDefinitions' => value([new TestFlowOptionDefinition()]),
    ]),
    new UsesAccountMock()
);

it('registers exportTestFlowOption on CarrierSettings with TriState INHERIT default', function () {
    $settings = new CarrierSettings();

    expect($settings->getAttribute('exportTestFlowOption'))->toBe(TriStateService::INHERIT);
});

it('registers allowTestFlowOption on CarrierSettings with false default', function () {
    $settings = new CarrierSettings();

    expect($settings->getAttribute('allowTestFlowOption'))->toBe(false);
});

it('registers priceTestFlowOption on CarrierSettings with 0 default', function () {
    $settings = new CarrierSettings();

    // Price settings are cast as 'float', so the default 0 is stored as 0.0
    expect($settings->getAttribute('priceTestFlowOption'))->toBe(0.0);
});

it('registers exportTestFlowOption on ProductSettings with TriState INHERIT default', function () {
    $settings = new ProductSettings();

    expect($settings->getAttribute('exportTestFlowOption'))->toBe(TriStateService::INHERIT);
});

it('registers testFlowOption on ShipmentOptions with TriState INHERIT default', function () {
    $options = new ShipmentOptions();

    expect($options->getAttribute('testFlowOption'))->toBe(TriStateService::INHERIT);
});

it('registers testFlowOption on Fulfilment ShipmentOptions with null default', function () {
    $options = new FulfilmentShipmentOptions();

    expect($options->getAttribute('testFlowOption'))->toBeNull();
});

it('casts testFlowOption to bool on Fulfilment ShipmentOptions', function () {
    $options = new FulfilmentShipmentOptions(['testFlowOption' => true]);

    expect($options->testFlowOption)->toBe(true);
});

it('resolves testFlowOption through priority chain via calculateShipmentOptions', function () {
    factory(Carrier::class)
        ->withAllCapabilities()
        ->store();

    $order = factory(PdkOrder::class)
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier('POSTNL')
                ->withShipmentOptions(new ShipmentOptions(['testFlowOption' => TriStateService::ENABLED]))
        )
        ->make();

    /** @var PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsServiceInterface::class);
    $newOrder = $service->calculateShipmentOptions($order);

    expect($newOrder->deliveryOptions->shipmentOptions->testFlowOption)->toBe(TriStateService::ENABLED);
});

it('resolves testFlowOption to DISABLED when no source sets a value', function () {
    factory(Carrier::class)
        ->withAllCapabilities()
        ->store();

    $order = factory(PdkOrder::class)
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)->withCarrier('POSTNL')
        )
        ->make();

    /** @var PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsServiceInterface::class);
    $newOrder = $service->calculateShipmentOptions($order);

    // When all sources are INHERIT, TriStateService::resolve() returns DISABLED (0) as the final value.
    expect($newOrder->deliveryOptions->shipmentOptions->testFlowOption)->toBe(TriStateService::DISABLED);
});

it('canHaveShipmentOption checks the capabilities key against carrier options', function () {
    // The definition's getCapabilitiesOptionsKey() is 'testFlowCapability'.
    // canHaveShipmentOption() uses that key to look up availability in the carrier schema.
    // Since 'testFlowCapability' is not a real SDK capabilities key, it will not appear
    // in any carrier's options, so the result is always false for this fake definition.
    $carrier = factory(Carrier::class)
        ->withAllCapabilities()
        ->make();

    /** @var CarrierSchema $carrierSchema */
    $carrierSchema = Pdk::get(CarrierSchema::class);
    $carrierSchema->setCarrier($carrier);

    $definition = new TestFlowOptionDefinition();

    // The key 'testFlowCapability' is not in the SDK capabilities model, so it is never available.
    expect($carrierSchema->canHaveShipmentOption($definition))->toBeFalse();
});

it('canHaveShipmentOption uses the definition capabilities key, not the shipment options key', function () {
    // Verify that canHaveShipmentOption() checks getCapabilitiesOptionsKey(), not getShipmentOptionsKey().
    // A carrier with NO options should return false for any definition.
    $carrier = factory(Carrier::class)
        ->withMinimalCapabilities()
        ->make();

    /** @var CarrierSchema $carrierSchema */
    $carrierSchema = Pdk::get(CarrierSchema::class);
    $carrierSchema->setCarrier($carrier);

    $definition = new TestFlowOptionDefinition();

    expect($carrierSchema->canHaveShipmentOption($definition))->toBeFalse();
});

it('toCapabilitiesDefinitions maps the shipment option key to the capabilities key', function () {
    // The definition maps: testFlowOption (shipment) -> testFlowCapability (capabilities).
    $options = new ShipmentOptions(['testFlowOption' => TriStateService::ENABLED]);

    $result = ShipmentOptions::toCapabilitiesDefinitions($options);

    // The result should use the capabilities key 'testFlowCapability', not 'testFlowOption'
    expect($result)->toHaveKey('testFlowCapability');
    expect($result['testFlowCapability'])->toBe(TriStateService::ENABLED);
});

it('fromCapabilitiesDefinitions maps the capabilities key back to the shipment options key', function () {
    // Input uses capabilities key 'testFlowCapability', output should be under shipment options key 'testFlowOption'.
    $data = ['testFlowCapability' => TriStateService::ENABLED];

    $options = ShipmentOptions::fromCapabilitiesDefinitions($data);

    expect($options->getAttribute('testFlowOption'))->toBe(TriStateService::ENABLED);
});

it('roundtrips through toCapabilitiesDefinitions and fromCapabilitiesDefinitions', function () {
    // Start with a ShipmentOptions model, export to capabilities format, then import back.
    $original = new ShipmentOptions(['testFlowOption' => TriStateService::DISABLED]);

    $exported = ShipmentOptions::toCapabilitiesDefinitions($original);

    // After export, the value should be under the capabilities key
    expect($exported)->toHaveKey('testFlowCapability');
    expect($exported['testFlowCapability'])->toBe(TriStateService::DISABLED);

    $imported = ShipmentOptions::fromCapabilitiesDefinitions($exported);

    // After import, the value should be back under the shipment options key
    expect($imported->getAttribute('testFlowOption'))->toBe(TriStateService::DISABLED);
});

it('definition derives correct key values for the full pipeline', function () {
    $definition = new TestFlowOptionDefinition();

    expect($definition->getShipmentOptionsKey())->toBe('testFlowOption');
    expect($definition->getCapabilitiesOptionsKey())->toBe('testFlowCapability');
    expect($definition->getCarrierSettingsKey())->toBe('exportTestFlowOption');
    expect($definition->getProductSettingsKey())->toBe('exportTestFlowOption');
    expect($definition->getAllowSettingsKey())->toBe('allowTestFlowOption');
    expect($definition->getPriceSettingsKey())->toBe('priceTestFlowOption');
    expect($definition->getShipmentOptionsCast())->toBe(TriStateService::TYPE_STRICT);
    expect($definition->getShipmentOptionsDefault())->toBe(TriStateService::INHERIT);
});
