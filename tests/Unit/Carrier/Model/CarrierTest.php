<?php

/** @noinspection PhpUndefinedMethodInspection,PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Model;

use MyParcelNL\Pdk\Account\Contract\AccountSettingsServiceInterface;
use MyParcelNL\Pdk\Base\Exception\ModelNotFoundException;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Carrier\Contract\CarrierRepositoryInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Proposition\Proposition;
use MyParcelNL\Pdk\Proposition\Service\PropositionService;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\Pdk\Tests\Uses\UsesEachMockPdkInstance;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefTypesCarrier;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2;

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
        ->withCarrier(RefCapabilitiesSharedCarrierV2::POSTNL)
        ->withPackageTypes(['PACKAGE', 'MAILBOX'])
        ->withDeliveryTypes(['STANDARD_DELIVERY', 'MORNING_DELIVERY'])
        ->make();

    expect($carrier->carrier)->toBe(RefCapabilitiesSharedCarrierV2::POSTNL)
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


it('looks up carriers from account using repository', function () {
    TestBootstrapper::forProposition(Proposition::MYPARCEL_ID);

    /** @var AccountSettingsServiceInterface $accountSettings */
    $accountSettings = Pdk::get(AccountSettingsServiceInterface::class);
    $carriers = $accountSettings->getCarriers();

    expect($carriers)->not->toBeEmpty();

    $firstCarrier = $carriers->first();
    $carrierName = $firstCarrier->carrier;

    /** @var CarrierRepositoryInterface $carrierRepository */
    $carrierRepository = Pdk::get(CarrierRepositoryInterface::class);

    // Look up carrier from repository
    $lookedUpCarrier = $carrierRepository->find($carrierName);

    expect($lookedUpCarrier)->not->toBeNull()
        ->and($lookedUpCarrier->carrier)->toBe($carrierName)
        ->and($lookedUpCarrier->packageTypes)->toBeArray()
        ->and($lookedUpCarrier->packageTypes)->not->toBeEmpty();
});

it('returns null from repository when carrier not found', function () {
    TestBootstrapper::forProposition(Proposition::MYPARCEL_ID);

    /** @var CarrierRepositoryInterface $carrierRepository */
    $carrierRepository = Pdk::get(CarrierRepositoryInterface::class);

    $carrier = $carrierRepository->find('UNKNOWN_CARRIER');

    expect($carrier)->toBeNull();
});

it('throws exception from repository when carrier not found with findOrFail', function () {
    TestBootstrapper::forProposition(Proposition::MYPARCEL_ID);

    /** @var CarrierRepositoryInterface $carrierRepository */
    $carrierRepository = Pdk::get(CarrierRepositoryInterface::class);

    $carrierRepository->findOrFail('UNKNOWN_CARRIER');
})->throws(ModelNotFoundException::class);

it('creates carrier directly with constructor without lookup', function () {
    $carrier = new Carrier(['carrier' => 'UNKNOWN_CARRIER']);

    // Constructor should create carrier with just the data passed, no lookup
    expect($carrier->carrier)->toBe('UNKNOWN_CARRIER')
        ->and($carrier->packageTypes)->toBeNull();
});
