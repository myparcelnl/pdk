<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Options\Helper;

use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Settings\Model\Settings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Tests\Uses\UsesSettingsMock;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

uses()->group('settings', 'tri-state');

usesShared(new UsesMockPdkInstance(), new UsesSettingsMock());

it('gets value with all settings disabled', function (string $carrierName, OrderOptionDefinitionInterface $definition) {
    $factory = factory(Settings::class)
        ->withCarrier($carrierName, [$definition->getCarrierSettingsKey() => false]);

    $factory->store();

    $settings  = array_keys($factory->make()->carrier->toArrayWithoutNull());
    $settings2 = array_keys(\MyParcelNL\Pdk\Facade\Settings::all()->carrier->toArrayWithoutNull());

    expect($settings)->toEqual($settings2);

    $order = factory(PdkOrder::class)
        ->withDeliveryOptions(factory(DeliveryOptions::class)->withCarrier($carrierName))
        ->make();

    $helper = new CarrierSettingsDefinitionHelper($order);

    expect($helper->get($definition))->toEqual(TriStateService::DISABLED);
})
    ->with('carrierNames')
    ->with('all shipment options');

it('gets value with all settings enabled', function (string $carrierName, OrderOptionDefinitionInterface $definition) {
    factory(Settings::class)
        ->withCarrier($carrierName, [$definition->getCarrierSettingsKey() => true])
        ->store();

    $order = factory(PdkOrder::class)
        ->withDeliveryOptions(factory(DeliveryOptions::class)->withCarrier($carrierName))
        ->make();

    $helper = new CarrierSettingsDefinitionHelper($order);

    expect($helper->get($definition))->toEqual(TriStateService::ENABLED);
})
    ->with('carrierNames')
    ->with('all shipment options');

