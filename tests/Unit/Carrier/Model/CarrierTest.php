<?php

/** @noinspection PhpUndefinedMethodInspection,PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Model;

use MyParcelNL\Pdk\Account\Contract\AccountSettingsServiceInterface;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Proposition\Proposition;
use MyParcelNL\Pdk\Proposition\Service\PropositionService;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\Pdk\Tests\Uses\UsesEachMockPdkInstance;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefTypesCarrier;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefTypesCarrierV2;

use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesEachMockPdkInstance());

it('creates carrier with SDK-backed properties', function (int $propositionId) {
    TestBootstrapper::forProposition($propositionId);

    /** @var AccountSettingsServiceInterface $accountSettings */
    $accountSettings = Pdk::get(AccountSettingsServiceInterface::class);
    $carriers = $accountSettings->getCarriers();

    expect($carriers)->not->toBeEmpty();

    $carrier = $carriers->first();

    // Test that SDK-backed properties are available
    expect($carrier)->toBeInstanceOf(Carrier::class)
        ->and($carrier->carrier)->toBeString() // 'carrier' property from SDK
        ->and($carrier->packageTypes)->toBeArray()
        ->and($carrier->deliveryTypes)->toBeArray()
        ->and($carrier->options)->not->toBeNull();
})->with([
    [Proposition::MYPARCEL_ID],
    [Proposition::SENDMYPARCEL_ID],
]);

it('creates carrier using factory with capabilities', function () {
    $carrier = factory(Carrier::class)
        ->withCarrier(RefTypesCarrierV2::POSTNL)
        ->withPackageTypes(['PACKAGE', 'MAILBOX'])
        ->withDeliveryTypes(['STANDARD_DELIVERY', 'MORNING_DELIVERY'])
        ->make();

    expect($carrier->carrier)->toBe(RefTypesCarrierV2::POSTNL)
        ->and($carrier->packageTypes)->toBe(['PACKAGE', 'MAILBOX'])
        ->and($carrier->deliveryTypes)->toBe(['STANDARD_DELIVERY', 'MORNING_DELIVERY']);
});

it('supports all capability types through factory', function () {
    $carrier = factory(Carrier::class)
        ->withAllCapabilities()
        ->make();

    expect($carrier->packageTypes)->toContain('PACKAGE')
        ->and($carrier->packageTypes)->toContain('MAILBOX')
        ->and($carrier->deliveryTypes)->toContain('STANDARD_DELIVERY')
        ->and($carrier->options)->toBeArray();
});

it('supports minimal capabilities through factory', function () {
    $carrier = factory(Carrier::class)
        ->withMinimalCapabilities()
        ->make();

    expect($carrier->packageTypes)->toBe(['PACKAGE'])
        ->and($carrier->deliveryTypes)->toBe(['STANDARD_DELIVERY'])
        ->and($carrier->options)->toBe([]);
});

// TODO: This test requires SDK-backed carrier properties to be properly serialized/deserialized
// Currently skipped because factory-created carriers don't preserve SDK model structure through lookup
it('looks up carriers from account when instantiated with name', function () {
    TestBootstrapper::forProposition(Proposition::MYPARCEL_ID);

    /** @var AccountSettingsServiceInterface $accountSettings */
    $accountSettings = Pdk::get(AccountSettingsServiceInterface::class);
    $carriers = $accountSettings->getCarriers();

    expect($carriers)->not->toBeEmpty();

    $firstCarrier = $carriers->first();
    $carrierName = $firstCarrier->carrier;

    // Create new carrier with just the name - should look up from account
    $lookedUpCarrier = new Carrier(['carrier' => $carrierName]);

    expect($lookedUpCarrier->carrier)->toBe($carrierName)
        ->and($lookedUpCarrier->packageTypes)->toBeArray()
        ->and($lookedUpCarrier->packageTypes)->not->toBeEmpty();
})->skip('SDK-backed properties not preserved through factory lookup');

it('returns empty carrier when name not found in account', function () {
    TestBootstrapper::forProposition(Proposition::MYPARCEL_ID);

    $carrier = new Carrier(['carrier' => 'UNKNOWN_CARRIER']);

    // Should create carrier with just the data passed, no lookup data
    expect($carrier->carrier)->toBe('UNKNOWN_CARRIER')
        ->and($carrier->packageTypes)->toBeNull();
});

it('merges passed data with looked up carrier data', function () {
    TestBootstrapper::forProposition(Proposition::MYPARCEL_ID);

    /** @var AccountSettingsServiceInterface $accountSettings */
    $accountSettings = Pdk::get(AccountSettingsServiceInterface::class);
    $carriers = $accountSettings->getCarriers();

    expect($carriers)->not->toBeEmpty();

    $firstCarrier = $carriers->first();
    $carrierName = $firstCarrier->carrier;

    // Create carrier with name (looks up) but override packageTypes
    $carrier = new Carrier([
        'carrier' => $carrierName,
        'packageTypes' => ['CUSTOM_PACKAGE'],
    ]);

    // Should use custom package types instead of looked up ones
    expect($carrier->carrier)->toBe($carrierName)
        ->and($carrier->packageTypes)->toBe(['CUSTOM_PACKAGE']);
});
