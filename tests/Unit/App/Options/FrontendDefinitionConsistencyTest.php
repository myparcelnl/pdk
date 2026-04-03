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
 * Phase 2 guardrail: proves the frontend's hardcoded delivery options service map doesn't
 * drift from definitions. Every allow* and price* entry in CONFIG_CARRIER_SETTINGS_MAP must
 * either be backed by a registered definition or be a known non-shipment-option entry
 * (delivery type / package type). Fails if someone adds a shipment option key to the
 * hardcoded map without creating a definition — forcing the Phase 2 migration.
 */

usesShared(new UsesEachMockPdkInstance());

it('documents which CONFIG_CARRIER_SETTINGS_MAP entries are backed by definitions', function () {
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
    $constants   = $reflection->getConstants();

    // @TODO: PHP 8.0+: use $reflection->getReflectionConstant('CONFIG_CARRIER_SETTINGS_MAP')
    $map = $constants['CONFIG_CARRIER_SETTINGS_MAP'];

    $unmapped = [];

    foreach ($map as $frontendKey => $settingsValue) {
        $isAllow = strpos($settingsValue, 'allow') === 0;
        $isPrice = strpos($settingsValue, 'price') === 0;

        if (! $isAllow && ! $isPrice) {
            continue;
        }

        $inDefinitions = in_array($settingsValue, $definitionAllowKeys, true)
            || in_array($settingsValue, $definitionPriceKeys, true);

        if (! $inDefinitions) {
            $unmapped[] = "{$frontendKey} => {$settingsValue}";
        }
    }

    // All unmapped entries must be delivery type or package type settings.
    // Shipment option settings (e.g. allowSignature, priceOnlyRecipient) must be backed by a definition.
    // This assertion documents the boundary: delivery type / package type keys are expected unmapped.
    foreach ($unmapped as $entry) {
        $isExpectedlyUnmapped = strpos($entry, 'DeliveryType') !== false
            || strpos($entry, 'PackageType') !== false
            || strpos($entry, 'DeliveryOptions') !== false
            || strpos($entry, 'StandardDelivery') !== false
            || strpos($entry, 'EveningDelivery') !== false
            || strpos($entry, 'MondayDelivery') !== false
            || strpos($entry, 'MorningDelivery') !== false
            || strpos($entry, 'PickupLocations') !== false;

        expect($isExpectedlyUnmapped)
            ->toBeTrue(
                "Unmapped entry \"{$entry}\" is not a recognised delivery-type or package-type key — a definition may be missing"
            );
    }
});
