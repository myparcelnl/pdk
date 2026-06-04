<?php

/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Service;

use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\Settings;
use MyParcelNL\Pdk\Tests\SdkApi\MockSdkApiHandler;
use MyParcelNL\Pdk\Tests\SdkApi\Response\ExampleCapabilitiesResponse;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Tests\Uses\UsesSdkApiMock;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2;

use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

uses()->group('checkout', 'capabilities');

usesShared(new UsesMockPdkInstance(), new UsesAccountMock(), new UsesSdkApiMock());

/**
 * Seed CarrierSettings::DELIVERY_OPTIONS_ENABLED for the given V2 carrier names so
 * the default carrier-enablement filter in getPackageTypeWeights() lets them through.
 *
 * @param  string[] $carrierNames
 */
function seedEnabledCarriers(array $carrierNames): void
{
    $settingsFactory = factory(Settings::class);

    foreach ($carrierNames as $carrierName) {
        $settingsFactory = $settingsFactory->withCarrier(
            $carrierName,
            factory(CarrierSettings::class, $carrierName)->withDeliveryOptionsEnabled(true)
        );
    }

    $settingsFactory->store();
}

it('takes the highest defined max weight when one carrier has no max defined', function () {
    seedEnabledCarriers([RefCapabilitiesSharedCarrierV2::POSTNL, RefCapabilitiesSharedCarrierV2::BPOST]);

    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([
        [
            'carrier'            => 'POSTNL',
            'contract'           => ['id' => 1, 'type' => 'MAIN'],
            'packageTypes'       => ['PACKAGE'],
            'options'            => (object) [],
            'physicalProperties' => [
                'weight' => [
                    'min' => ['value' => 1, 'unit' => 'g'],
                    'max' => ['value' => 23000, 'unit' => 'g'],
                ],
            ],
            'deliveryTypes'      => ['STANDARD_DELIVERY'],
            'transactionTypes'   => [],
            'collo'              => ['max' => 1],
        ],
        [
            'carrier'            => 'BPOST',
            'contract'           => ['id' => 2, 'type' => 'MAIN'],
            'packageTypes'       => ['PACKAGE'],
            'options'            => (object) [],
            // No physicalProperties → no max defined.
            'deliveryTypes'      => ['STANDARD_DELIVERY'],
            'transactionTypes'   => [],
            'collo'              => ['max' => 1],
        ],
    ]));

    /** @var CapabilitiesValidationService $service */
    $service = Pdk::get(CapabilitiesValidationService::class);

    $weights = $service->getPackageTypeWeights('NL', ['package' => 'PACKAGE']);

    expect($weights)->toBe(['package' => 23000]);
});

it('returns null when no carrier in the response defines a max weight', function () {
    seedEnabledCarriers([RefCapabilitiesSharedCarrierV2::POSTNL]);

    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([
        [
            'carrier'            => 'POSTNL',
            'contract'           => ['id' => 1, 'type' => 'MAIN'],
            'packageTypes'       => ['PACKAGE'],
            'options'            => (object) [],
            'deliveryTypes'      => ['STANDARD_DELIVERY'],
            'transactionTypes'   => [],
            'collo'              => ['max' => 1],
        ],
    ]));

    /** @var CapabilitiesValidationService $service */
    $service = Pdk::get(CapabilitiesValidationService::class);

    $weights = $service->getPackageTypeWeights('NL', ['package' => 'PACKAGE']);

    expect($weights)->toBe(['package' => null]);
});

it('excludes weights from carriers without delivery options enabled', function () {
    // Only POSTNL is enabled in shop settings; BPOST has a heavier defined max
    // but should not contribute to the per-type aggregation.
    seedEnabledCarriers([RefCapabilitiesSharedCarrierV2::POSTNL]);

    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([
        [
            'carrier'            => 'POSTNL',
            'contract'           => ['id' => 1, 'type' => 'MAIN'],
            'packageTypes'       => ['PACKAGE'],
            'options'            => (object) [],
            'physicalProperties' => [
                'weight' => [
                    'min' => ['value' => 1, 'unit' => 'g'],
                    'max' => ['value' => 10000, 'unit' => 'g'],
                ],
            ],
            'deliveryTypes'      => ['STANDARD_DELIVERY'],
            'transactionTypes'   => [],
            'collo'              => ['max' => 1],
        ],
        [
            'carrier'            => 'BPOST',
            'contract'           => ['id' => 2, 'type' => 'MAIN'],
            'packageTypes'       => ['PACKAGE'],
            'options'            => (object) [],
            'physicalProperties' => [
                'weight' => [
                    'min' => ['value' => 1, 'unit' => 'g'],
                    'max' => ['value' => 23000, 'unit' => 'g'],
                ],
            ],
            'deliveryTypes'      => ['STANDARD_DELIVERY'],
            'transactionTypes'   => [],
            'collo'              => ['max' => 1],
        ],
    ]));

    /** @var CapabilitiesValidationService $service */
    $service = Pdk::get(CapabilitiesValidationService::class);

    $weights = $service->getPackageTypeWeights('NL', ['package' => 'PACKAGE']);

    expect($weights)->toBe(['package' => 10000]);
});

it('includes weights from all carriers when filter is opted out', function () {
    // POSTNL is the only enabled carrier in settings, but the opt-out flag
    // bypasses the filter so BPOST's heavier max still contributes.
    seedEnabledCarriers([RefCapabilitiesSharedCarrierV2::POSTNL]);

    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([
        [
            'carrier'            => 'POSTNL',
            'contract'           => ['id' => 1, 'type' => 'MAIN'],
            'packageTypes'       => ['PACKAGE'],
            'options'            => (object) [],
            'physicalProperties' => [
                'weight' => [
                    'min' => ['value' => 1, 'unit' => 'g'],
                    'max' => ['value' => 10000, 'unit' => 'g'],
                ],
            ],
            'deliveryTypes'      => ['STANDARD_DELIVERY'],
            'transactionTypes'   => [],
            'collo'              => ['max' => 1],
        ],
        [
            'carrier'            => 'BPOST',
            'contract'           => ['id' => 2, 'type' => 'MAIN'],
            'packageTypes'       => ['PACKAGE'],
            'options'            => (object) [],
            'physicalProperties' => [
                'weight' => [
                    'min' => ['value' => 1, 'unit' => 'g'],
                    'max' => ['value' => 23000, 'unit' => 'g'],
                ],
            ],
            'deliveryTypes'      => ['STANDARD_DELIVERY'],
            'transactionTypes'   => [],
            'collo'              => ['max' => 1],
        ],
    ]));

    /** @var CapabilitiesValidationService $service */
    $service = Pdk::get(CapabilitiesValidationService::class);

    $weights = $service->getPackageTypeWeights('NL', ['package' => 'PACKAGE'], false);

    expect($weights)->toBe(['package' => 23000]);
});

it('supportsReturns is true when /capabilities (direction: INBOUND) returns a non-empty result', function () {
    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([
        [
            'carrier'            => 'POSTNL',
            'contract'           => ['id' => 1, 'type' => 'MAIN'],
            'packageTypes'       => ['PACKAGE'],
            'options'            => (object) [],
            'deliveryTypes'      => ['STANDARD_DELIVERY'],
            'transactionTypes'   => [],
            'collo'              => ['max' => 1],
        ],
    ]));

    $carrier = factory(Carrier::class)->withCarrier('POSTNL')->make();

    /** @var CapabilitiesValidationService $service */
    $service = Pdk::get(CapabilitiesValidationService::class);

    expect($service->supportsReturns($carrier, 'NL'))->toBeTrue();
});

it('supportsReturns is false when /capabilities (direction: INBOUND) returns an empty result', function () {
    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([]));

    $carrier = factory(Carrier::class)->withCarrier('POSTNL')->make();

    /** @var CapabilitiesValidationService $service */
    $service = Pdk::get(CapabilitiesValidationService::class);

    expect($service->supportsReturns($carrier, 'NL'))->toBeFalse();
});

/**
 * Fetch one deserialized capability from the mock queue for direct testing of
 * methods that accept a RefCapabilitiesResponseCapabilityV2.
 *
 * @param  array $capabilityArray Raw shape matching ExampleCapabilitiesResponse entries
 *
 * @return \MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesResponseCapabilityV2
 */
function fetchCapability(array $capabilityArray)
{
    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([$capabilityArray]));

    /** @var CapabilitiesValidationService $service */
    $service      = Pdk::get(CapabilitiesValidationService::class);
    $capabilities = $service->getRepository()->getCapabilities([
        'recipient'    => ['country_code' => 'NL'],
        'package_type' => 'PACKAGE',
        // Unique nonce so each test gets its own cache key
        '__test_nonce' => uniqid('cap', true),
    ]);

    return $capabilities[0];
}

it('supportsWeight returns true when capability has no physicalProperties', function () {
    $capability = fetchCapability([
        'carrier'          => 'POSTNL',
        'contract'         => ['id' => 1, 'type' => 'MAIN'],
        'packageTypes'     => ['PACKAGE'],
        'options'          => (object) [],
        'deliveryTypes'    => ['STANDARD_DELIVERY'],
        'transactionTypes' => [],
        'collo'            => ['max' => 1],
    ]);

    /** @var CapabilitiesValidationService $service */
    $service = Pdk::get(CapabilitiesValidationService::class);

    expect($service->supportsWeight($capability, 5000))->toBeTrue();
});

it('supportsWeight returns true when physicalProperties has no weight constraint', function () {
    $capability = fetchCapability([
        'carrier'            => 'POSTNL',
        'contract'           => ['id' => 1, 'type' => 'MAIN'],
        'packageTypes'       => ['PACKAGE'],
        'options'            => (object) [],
        'physicalProperties' => (object) [],
        'deliveryTypes'      => ['STANDARD_DELIVERY'],
        'transactionTypes'   => [],
        'collo'              => ['max' => 1],
    ]);

    /** @var CapabilitiesValidationService $service */
    $service = Pdk::get(CapabilitiesValidationService::class);

    expect($service->supportsWeight($capability, 5000))->toBeTrue();
});

it('supportsWeight accepts weights within [min, max] expressed in grams', function () {
    $capability = fetchCapability([
        'carrier'            => 'POSTNL',
        'contract'           => ['id' => 1, 'type' => 'MAIN'],
        'packageTypes'       => ['PACKAGE'],
        'options'            => (object) [],
        'physicalProperties' => [
            'weight' => [
                'min' => ['value' => 1000, 'unit' => 'g'],
                'max' => ['value' => 20000, 'unit' => 'g'],
            ],
        ],
        'deliveryTypes'      => ['STANDARD_DELIVERY'],
        'transactionTypes'   => [],
        'collo'              => ['max' => 1],
    ]);

    /** @var CapabilitiesValidationService $service */
    $service = Pdk::get(CapabilitiesValidationService::class);

    expect($service->supportsWeight($capability, 5000))->toBeTrue();
});

it('supportsWeight rejects weight above max', function () {
    $capability = fetchCapability([
        'carrier'            => 'POSTNL',
        'contract'           => ['id' => 1, 'type' => 'MAIN'],
        'packageTypes'       => ['PACKAGE'],
        'options'            => (object) [],
        'physicalProperties' => [
            'weight' => [
                'min' => ['value' => 1000, 'unit' => 'g'],
                'max' => ['value' => 20000, 'unit' => 'g'],
            ],
        ],
        'deliveryTypes'      => ['STANDARD_DELIVERY'],
        'transactionTypes'   => [],
        'collo'              => ['max' => 1],
    ]);

    /** @var CapabilitiesValidationService $service */
    $service = Pdk::get(CapabilitiesValidationService::class);

    expect($service->supportsWeight($capability, 25000))->toBeFalse();
});

it('supportsWeight rejects weight below min', function () {
    $capability = fetchCapability([
        'carrier'            => 'POSTNL',
        'contract'           => ['id' => 1, 'type' => 'MAIN'],
        'packageTypes'       => ['PACKAGE'],
        'options'            => (object) [],
        'physicalProperties' => [
            'weight' => [
                'min' => ['value' => 1000, 'unit' => 'g'],
                'max' => ['value' => 20000, 'unit' => 'g'],
            ],
        ],
        'deliveryTypes'      => ['STANDARD_DELIVERY'],
        'transactionTypes'   => [],
        'collo'              => ['max' => 1],
    ]);

    /** @var CapabilitiesValidationService $service */
    $service = Pdk::get(CapabilitiesValidationService::class);

    expect($service->supportsWeight($capability, 500))->toBeFalse();
});

it('supportsWeight normalizes kg constraints to grams before comparing', function () {
    // API returns max as 23 kg (= 23 000 g). A weight of 15 000 g must still fit;
    // a weight of 25 000 g must not. Without unit normalization the 23 would be
    // treated as 23 g and even 100 g would be rejected.
    $capability = fetchCapability([
        'carrier'            => 'POSTNL',
        'contract'           => ['id' => 1, 'type' => 'MAIN'],
        'packageTypes'       => ['PACKAGE'],
        'options'            => (object) [],
        'physicalProperties' => [
            'weight' => [
                'min' => ['value' => 1, 'unit' => 'kg'],
                'max' => ['value' => 23, 'unit' => 'kg'],
            ],
        ],
        'deliveryTypes'      => ['STANDARD_DELIVERY'],
        'transactionTypes'   => [],
        'collo'              => ['max' => 1],
    ]);

    /** @var CapabilitiesValidationService $service */
    $service = Pdk::get(CapabilitiesValidationService::class);

    expect($service->supportsWeight($capability, 15000))->toBeTrue()
        ->and($service->supportsWeight($capability, 25000))->toBeFalse();
});

it('supportsWeight skips the min check when only max is defined', function () {
    $capability = fetchCapability([
        'carrier'            => 'POSTNL',
        'contract'           => ['id' => 1, 'type' => 'MAIN'],
        'packageTypes'       => ['PACKAGE'],
        'options'            => (object) [],
        'physicalProperties' => [
            'weight' => [
                'max' => ['value' => 20000, 'unit' => 'g'],
            ],
        ],
        'deliveryTypes'      => ['STANDARD_DELIVERY'],
        'transactionTypes'   => [],
        'collo'              => ['max' => 1],
    ]);

    /** @var CapabilitiesValidationService $service */
    $service = Pdk::get(CapabilitiesValidationService::class);

    // Below any plausible min, but within max, must pass: min is unconstrained.
    expect($service->supportsWeight($capability, 1))->toBeTrue();
});

it('supportsWeight skips the max check when only min is defined', function () {
    $capability = fetchCapability([
        'carrier'            => 'POSTNL',
        'contract'           => ['id' => 1, 'type' => 'MAIN'],
        'packageTypes'       => ['PACKAGE'],
        'options'            => (object) [],
        'physicalProperties' => [
            'weight' => [
                'min' => ['value' => 1000, 'unit' => 'g'],
            ],
        ],
        'deliveryTypes'      => ['STANDARD_DELIVERY'],
        'transactionTypes'   => [],
        'collo'              => ['max' => 1],
    ]);

    /** @var CapabilitiesValidationService $service */
    $service = Pdk::get(CapabilitiesValidationService::class);

    // Far above any plausible max, but within min, must pass: max is unconstrained.
    expect($service->supportsWeight($capability, 999999))->toBeTrue();
});
