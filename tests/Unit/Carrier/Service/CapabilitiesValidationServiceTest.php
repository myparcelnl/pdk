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
