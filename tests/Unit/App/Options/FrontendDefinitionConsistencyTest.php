<?php

/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Options;

use MyParcelNL\Pdk\App\DeliveryOptions\Service\DeliveryOptionsService;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Uses\UsesEachMockPdkInstance;
use ReflectionClass;

use function MyParcelNL\Pdk\Tests\usesShared;

/**
 * Phase 2 guardrail: proves that NON_DEFINITION_CARRIER_SETTINGS_MAP contains only
 * delivery-type, package-type, and other non-shipment-option entries. Shipment option
 * allow/price keys must NOT appear in this constant — they are built dynamically from
 * OrderOptionDefinitions via getCarrierSettingsMap().
 */

usesShared(new UsesEachMockPdkInstance());

it('documents which NON_DEFINITION_CARRIER_SETTINGS_MAP entries are backed by definitions', function () {
    $definitions = Pdk::get('orderOptionDefinitions');

    $definitionAllowKeys = [];
    $definitionPriceKeys = [];

    foreach ($definitions as $definition) {
        $allowKey = $definition->getAllowSettingsKey();
        $priceKey = $definition->getPriceSettingsKey();

        if ($allowKey) {
            $definitionAllowKeys[] = $allowKey;
        }

        if ($priceKey) {
            $definitionPriceKeys[] = $priceKey;
        }
    }

    $reflection = new ReflectionClass(DeliveryOptionsService::class);
    $constants  = $reflection->getConstants();

    // @TODO: PHP 8.0+: use $reflection->getReflectionConstant('NON_DEFINITION_CARRIER_SETTINGS_MAP')
    $map = $constants['NON_DEFINITION_CARRIER_SETTINGS_MAP'];

    // No allow* or price* shipment option keys backed by a definition should exist in this constant.
    // If they appear here, they should be moved to the definitions instead.
    foreach ($map as $frontendKey => $settingsValue) {
        $inDefinitions = in_array($settingsValue, $definitionAllowKeys, true)
            || in_array($settingsValue, $definitionPriceKeys, true);

        expect($inDefinitions)
            ->toBeFalse(
                "Entry \"{$frontendKey} => {$settingsValue}\" is backed by a definition and should be removed from NON_DEFINITION_CARRIER_SETTINGS_MAP"
            );
    }
});
