<?php

/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('fromCapabilitiesDefinitions preserves values when capabilities key matches model key', function () {
    // When the capabilities key and model key are the same (e.g. 'tracked', 'insurance'),
    // fromCapabilitiesDefinitions must not lose the value.
    $result = ShipmentOptions::fromCapabilitiesDefinitions([
        'tracked'   => TriStateService::DISABLED,
        'insurance' => TriStateService::DISABLED,
    ]);

    expect($result->tracked)->toBe(TriStateService::DISABLED)
        ->and($result->insurance)->toBe(TriStateService::DISABLED);
});

it('fromCapabilitiesDefinitions maps capability keys to model keys', function () {
    // When the capabilities key differs from the model key (e.g. requiresSignature → signature),
    // the value should be mapped correctly.
    $result = ShipmentOptions::fromCapabilitiesDefinitions([
        'requiresSignature' => TriStateService::DISABLED,
    ]);

    expect($result->signature)->toBe(TriStateService::DISABLED);
});

it('fromCapabilitiesDefinitions preserves all values in export-like merge scenario', function () {
    // Simulate merge of existing order shipmentOptions (model keys) with request data (capability keys)
    $existingOptions = [
        'signature' => TriStateService::ENABLED,
        'tracked'   => TriStateService::INHERIT,
    ];

    $requestOptions = [
        'requiresSignature' => TriStateService::DISABLED,
        'tracked'           => TriStateService::DISABLED,
        'insurance'         => TriStateService::INHERIT,
    ];

    // After array_replace_recursive, both model and capability keys coexist
    $merged = \array_replace_recursive($existingOptions, $requestOptions);
    $result = ShipmentOptions::fromCapabilitiesDefinitions($merged);

    // requiresSignature:0 → mapped to signature, overwriting existing 1
    expect($result->signature)->toBe(TriStateService::DISABLED)
        // tracked:0 → same key as model, must not be lost
        ->and($result->tracked)->toBe(TriStateService::DISABLED)
        // insurance:-1 → same key as model, must not be lost
        ->and($result->insurance)->toBe(TriStateService::INHERIT);
});
