<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Factory;

use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Tests\Uses\UsesEachMockPdkInstance;
use function MyParcelNL\Pdk\Tests\Datasets\getAllShipmentOptions;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;
use function Spatie\Snapshots\assertMatchesSnapshot;

usesShared(new UsesEachMockPdkInstance());

it('saves carrier settings correctly', function (string $carrierName) {
    $options = getAllShipmentOptions();

    $names = array_map(function (OrderOptionDefinitionInterface $definition) {
        return $definition->getCarrierSettingsKey();
    }, $options);

    $settingsFactory = factory(\MyParcelNL\Pdk\Settings\Model\Settings::class)
        ->fromScratch()
        ->withCarrier($carrierName, array_combine($names, array_fill(0, count($names), true)))
        ->store();

    $settings = $settingsFactory->make();

    $carrierSettings1 = $settings->carrier->toArrayWithoutNull();

    $carrierSettings2 = Settings::all()->carrier->toArrayWithoutNull();

    expect($carrierSettings1)
        ->toHaveLength(1)
        ->and($carrierSettings1)
        ->toHaveKeys([$carrierName])
        ->and($carrierSettings2)
        ->toHaveLength(1)
        ->and($carrierSettings2)
        ->toHaveKeys([$carrierName])
        ->and($carrierSettings1)
        ->toEqual($carrierSettings2);

    assertMatchesSnapshot($carrierSettings2);
})->with('carrierNames');
