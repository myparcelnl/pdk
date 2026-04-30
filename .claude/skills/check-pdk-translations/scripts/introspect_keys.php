<?php

declare(strict_types=1);

/*
 * Enumerate the translation keys the PDK runtime expects, based on:
 *   - PDK Definition classes in src/App/Options/Definition (allow/price/carrier/product/shipment-options/capabilities keys)
 *   - SDK constant classes (delivery types, package types, carriers)
 *   - createGenericLabel('foo') calls in CarrierSettingsItemView (static dividers)
 *
 * Output: a single JSON document on stdout. The bash wrapper diffs it against
 * the plugin's generated translation files for each supported language.
 *
 * Designed to be run via `docker compose run --rm -T php php <this>` from PDK root.
 */

$pdkRoot = realpath(__DIR__ . '/../../../..');
require $pdkRoot . '/vendor/autoload.php';

$snake = static function (string $camel): string {
    return strtolower((string) preg_replace('/([A-Z])/', '_$1', lcfirst($camel)));
};

$definitionsDir   = $pdkRoot . '/src/App/Options/Definition';
$shipmentOptions  = [];
$settingsCarrier  = [];
$settingsProduct  = [];

foreach (glob($definitionsDir . '/*Definition.php') ?: [] as $file) {
    $basename = basename($file, '.php');
    if ($basename === 'AbstractOrderOptionDefinition') {
        continue;
    }
    $fqcn = "MyParcelNL\\Pdk\\App\\Options\\Definition\\{$basename}";
    if (! class_exists($fqcn)) {
        continue;
    }
    $reflection = new ReflectionClass($fqcn);
    if ($reflection->isAbstract()) {
        continue;
    }
    $definition = $reflection->newInstance();

    $cap                = $definition->getCapabilitiesOptionsKey();
    $shipmentOptionsKey = $definition->getShipmentOptionsKey();

    if ($cap !== null && $cap !== '') {
        $key            = "shipment_options_{$cap}";
        $legacy         = null;
        if ($shipmentOptionsKey !== null && $shipmentOptionsKey !== '') {
            $legacyCandidate = "shipment_options_{$snake($shipmentOptionsKey)}";
            if ($legacyCandidate !== $key) {
                $legacy = $legacyCandidate;
            }
        }
        $shipmentOptions[] = ['key' => $key, 'legacy' => $legacy];
    }

    $perOptionCarrierKeys = [
        $definition->getAllowSettingsKey(),
        $definition->getPriceSettingsKey(),
        $definition->getCarrierSettingsKey(),
    ];
    foreach ($perOptionCarrierKeys as $camel) {
        if ($camel === null || $camel === '') {
            continue;
        }
        $settingsCarrier[] = "settings_carrier_{$snake($camel)}";
    }

    $product = $definition->getProductSettingsKey();
    if ($product !== null && $product !== '') {
        $settingsProduct[] = "settings_product_{$snake($product)}";
    }
}

// Static dividers from CarrierSettingsItemView (createGenericLabel('foo') -> settings_carrier_<foo>_title).
// CarrierSettingsItemView always uses the 'carrier' label prefix (CarrierSettings::ID).
// Dividers render with `_title`; `_description` exists for some but is optional.
$staticDividers = [];
$viewFile       = $pdkRoot . '/src/Frontend/View/CarrierSettingsItemView.php';
if (is_file($viewFile)) {
    $contents = (string) file_get_contents($viewFile);
    if (preg_match_all("/createGenericLabel\\(['\"]([a-z_]+)['\"]\\)/", $contents, $matches) > 0) {
        foreach (array_unique($matches[1]) as $divider) {
            $staticDividers[] = "settings_carrier_{$divider}_title";
        }
    }
}

$constantsAsKeys = static function (string $fqcn, string $prefix): array {
    if (! class_exists($fqcn)) {
        return [];
    }
    $r    = new ReflectionClass($fqcn);
    $keys = [];
    foreach ($r->getConstants() as $value) {
        if (! is_string($value) || $value === '') {
            continue;
        }
        $keys[] = $prefix . '_' . strtolower($value);
    }
    return array_values(array_unique($keys));
};

echo json_encode([
    'shipment_options'         => $shipmentOptions,
    'settings_carrier'         => array_values(array_unique($settingsCarrier)),
    'settings_product'         => array_values(array_unique($settingsProduct)),
    'settings_carrier_dividers'=> $staticDividers,
    'delivery_type'            => $constantsAsKeys('MyParcelNL\\Sdk\\Client\\Generated\\CoreApi\\Model\\ShipmentDefsDeliveryOptionsDeliveryNameV2', 'delivery_type'),
    'package_type'             => $constantsAsKeys('MyParcelNL\\Sdk\\Client\\Generated\\CoreApi\\Model\\RefShipmentPackageTypeV2', 'package_type'),
    'carrier'                  => $constantsAsKeys('MyParcelNL\\Sdk\\Client\\Generated\\CoreApi\\Model\\RefCapabilitiesSharedCarrierV2', 'carrier'),
], JSON_THROW_ON_ERROR);
