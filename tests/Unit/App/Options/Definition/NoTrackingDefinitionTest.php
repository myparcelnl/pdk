<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Options\Definition;

it('maps to the no tracking shipment option', function () {
    expect((new NoTrackingDefinition())->getShipmentOptionsKey())->toBe('noTracking');
});

it('maps to the no tracking capabilities option', function () {
    expect((new NoTrackingDefinition())->getCapabilitiesOptionsKey())->toBe('noTracking');
});

it('cannot be given a surcharge or a consumer toggle', function () {
    // Not tracking is cheaper than tracking, so a surcharge would mean entering a negative price.
    // The consumer must not be able to switch it off in the checkout either.
    $definition = new NoTrackingDefinition();

    expect($definition->getPriceSettingsKey())->toBeNull()
        ->and($definition->getAllowSettingsKey())->toBeNull();
});

it('can be set per carrier and per product', function () {
    // Both keys are inherited from AbstractOrderOptionDefinition. Returning null there would opt the
    // option out of that settings level, so these assertions guard the carrier default and the
    // per-product override that mirror the old tracked setting.
    $definition = new NoTrackingDefinition();

    expect($definition->getCarrierSettingsKey())->toBe('exportNoTracking')
        ->and($definition->getProductSettingsKey())->toBe('exportNoTracking');
});
