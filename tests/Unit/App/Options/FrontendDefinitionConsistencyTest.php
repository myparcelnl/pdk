<?php

/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Options;

use MyParcelNL\Pdk\App\DeliveryOptions\Service\DeliveryOptionsService;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Uses\UsesEachMockPdkInstance;
use ReflectionMethod;

use function MyParcelNL\Pdk\Tests\usesShared;

/**
 * Guardrail: proves the hand-curated portion of getCarrierSettingsMap() does
 * not shadow keys already produced by OrderOptionDefinitions. Shipment-option
 * allow/price keys must come from definitions; non-shipment-option entries
 * (delivery types, package types, etc.) live in the hand-curated lists.
 */

usesShared(new UsesEachMockPdkInstance());

it('hand-curated overrides do not shadow definition-derived allow or price keys', function () {
    $definitions    = Pdk::get('orderOptionDefinitions');
    $definitionKeys = [];

    foreach ($definitions as $definition) {
        $allowKey = $definition->getAllowSettingsKey();
        $priceKey = $definition->getPriceSettingsKey();

        if ($allowKey) {
            $definitionKeys[] = $allowKey;
        }

        if ($priceKey) {
            $definitionKeys[] = $priceKey;
        }
    }

    $method = new ReflectionMethod(DeliveryOptionsService::class, 'getCarrierSettingsMap');
    $method->setAccessible(true);
    $map = $method->invoke(null);

    foreach ($map as $frontendKey => $settingsValue) {
        // Skip rows that came from the definitions loop (key === value === a known definition key).
        $isDefinitionRow = in_array($frontendKey, $definitionKeys, true) && $frontendKey === $settingsValue;

        if ($isDefinitionRow) {
            continue;
        }

        expect(in_array($settingsValue, $definitionKeys, true))
            ->toBeFalse(
                "Hand-curated entry \"{$frontendKey} => {$settingsValue}\" shadows a definition-derived key"
            );
    }
});
