<?php

/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Repository;

use InvalidArgumentException;
use MyParcelNL\Pdk\Base\Exception\ModelNotFoundException;
use MyParcelNL\Pdk\Carrier\Contract\CarrierRepositoryInterface;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Proposition\Proposition;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkFactory;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\Pdk\Tests\Uses\UsesEachMockPdkInstance;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefTypesCarrier;

use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesEachMockPdkInstance());

// all()

it('returns all carriers from the account', function () {
    TestBootstrapper::forProposition(Proposition::MYPARCEL_ID);

    /** @var CarrierRepositoryInterface $repository */
    $repository = Pdk::get(CarrierRepositoryInterface::class);

    $carriers = $repository->all();

    expect($carriers)->not->toBeEmpty()
        ->and($carriers->first())->toBeInstanceOf(Carrier::class);
});

it('returns an empty collection when no account is set', function () {
    MockPdkFactory::create();

    /** @var CarrierRepositoryInterface $repository */
    $repository = Pdk::get(CarrierRepositoryInterface::class);

    $carriers = $repository->all();

    expect($carriers)->toBeEmpty();
});

// find()

it('finds a carrier by V2 name', function () {
    TestBootstrapper::forProposition(Proposition::MYPARCEL_ID);

    /** @var CarrierRepositoryInterface $repository */
    $repository = Pdk::get(CarrierRepositoryInterface::class);

    $carrier = $repository->find(RefCapabilitiesSharedCarrierV2::POSTNL);

    expect($carrier)->toBeInstanceOf(Carrier::class)
        ->and($carrier->carrier)->toBe(RefCapabilitiesSharedCarrierV2::POSTNL);
});

it('returns null from find() when carrier is not in account', function () {
    TestBootstrapper::forProposition(Proposition::MYPARCEL_ID);

    /** @var CarrierRepositoryInterface $repository */
    $repository = Pdk::get(CarrierRepositoryInterface::class);

    expect($repository->find('UNKNOWN_CARRIER_XYZ'))->toBeNull();
});

// findOrFail()

it('returns carrier from findOrFail() when found', function () {
    TestBootstrapper::forProposition(Proposition::MYPARCEL_ID);

    /** @var CarrierRepositoryInterface $repository */
    $repository = Pdk::get(CarrierRepositoryInterface::class);

    $carrier = $repository->findOrFail(RefCapabilitiesSharedCarrierV2::POSTNL);

    expect($carrier)->toBeInstanceOf(Carrier::class)
        ->and($carrier->carrier)->toBe(RefCapabilitiesSharedCarrierV2::POSTNL);
});

it('throws ModelNotFoundException from findOrFail() when carrier is not in account', function () {
    TestBootstrapper::forProposition(Proposition::MYPARCEL_ID);

    /** @var CarrierRepositoryInterface $repository */
    $repository = Pdk::get(CarrierRepositoryInterface::class);

    $repository->findOrFail('UNKNOWN_CARRIER_XYZ');
})->throws(ModelNotFoundException::class);

// findAll()

it('returns a collection of matched carriers from findAll()', function () {
    TestBootstrapper::forProposition(Proposition::MYPARCEL_ID);

    /** @var CarrierRepositoryInterface $repository */
    $repository = Pdk::get(CarrierRepositoryInterface::class);

    $carriers = $repository->findAll([
        RefCapabilitiesSharedCarrierV2::POSTNL,
        RefCapabilitiesSharedCarrierV2::DPD,
    ]);

    expect($carriers)->toHaveCount(2)
        ->and($carriers->firstWhere('carrier', RefCapabilitiesSharedCarrierV2::POSTNL))->not->toBeNull()
        ->and($carriers->firstWhere('carrier', RefCapabilitiesSharedCarrierV2::DPD))->not->toBeNull();
});

it('silently skips unknown names in findAll()', function () {
    TestBootstrapper::forProposition(Proposition::MYPARCEL_ID);

    /** @var CarrierRepositoryInterface $repository */
    $repository = Pdk::get(CarrierRepositoryInterface::class);

    $carriers = $repository->findAll([
        RefCapabilitiesSharedCarrierV2::POSTNL,
        'UNKNOWN_CARRIER_XYZ',
    ]);

    expect($carriers)->toHaveCount(1)
        ->and($carriers->first()->carrier)->toBe(RefCapabilitiesSharedCarrierV2::POSTNL);
});

it('returns an empty collection from findAll() when all names are unknown', function () {
    TestBootstrapper::forProposition(Proposition::MYPARCEL_ID);

    /** @var CarrierRepositoryInterface $repository */
    $repository = Pdk::get(CarrierRepositoryInterface::class);

    expect($repository->findAll(['UNKNOWN_A', 'UNKNOWN_B']))->toBeEmpty();
});

it('returns an empty collection from findAll() when given an empty array', function () {
    TestBootstrapper::forProposition(Proposition::MYPARCEL_ID);

    /** @var CarrierRepositoryInterface $repository */
    $repository = Pdk::get(CarrierRepositoryInterface::class);

    expect($repository->findAll([]))->toBeEmpty();
});

// findByLegacyName()

it('finds a carrier by its legacy name', function (string $legacyName, string $expectedV2Name) {
    TestBootstrapper::forProposition(Proposition::MYPARCEL_ID);

    /** @var CarrierRepositoryInterface $repository */
    $repository = Pdk::get(CarrierRepositoryInterface::class);

    $carrier = $repository->findByLegacyName($legacyName);

    expect($carrier)->toBeInstanceOf(Carrier::class)
        ->and($carrier->carrier)->toBe($expectedV2Name);
})->with([
    'postnl'    => [Carrier::CARRIER_POSTNL_LEGACY_NAME, RefCapabilitiesSharedCarrierV2::POSTNL],
    'dpd'       => [Carrier::CARRIER_DPD_LEGACY_NAME, RefCapabilitiesSharedCarrierV2::DPD],
    'bpost'     => [Carrier::CARRIER_BPOST_LEGACY_NAME, RefCapabilitiesSharedCarrierV2::BPOST],
    'dhlforyou' => [Carrier::CARRIER_DHL_FOR_YOU_LEGACY_NAME, RefCapabilitiesSharedCarrierV2::DHL_FOR_YOU],
]);

it('throws InvalidArgumentException from findByLegacyName() for an unknown legacy name', function () {
    TestBootstrapper::forProposition(Proposition::MYPARCEL_ID);

    /** @var CarrierRepositoryInterface $repository */
    $repository = Pdk::get(CarrierRepositoryInterface::class);

    $repository->findByLegacyName('not_a_real_carrier');
})->throws(InvalidArgumentException::class);

// findByLegacyId()

it('finds a carrier by its legacy numeric ID', function (int $legacyId, string $expectedV2Name) {
    TestBootstrapper::forProposition(Proposition::MYPARCEL_ID);

    /** @var CarrierRepositoryInterface $repository */
    $repository = Pdk::get(CarrierRepositoryInterface::class);

    $carrier = $repository->findByLegacyId($legacyId);

    expect($carrier)->toBeInstanceOf(Carrier::class)
        ->and($carrier->carrier)->toBe($expectedV2Name);
})->with([
    'PostNL (id=1)' => [RefTypesCarrier::POSTNL, RefCapabilitiesSharedCarrierV2::POSTNL],
    'bpost (id=2)'  => [RefTypesCarrier::BPOST, RefCapabilitiesSharedCarrierV2::BPOST],
    'DPD (id=4)'    => [RefTypesCarrier::DPD, RefCapabilitiesSharedCarrierV2::DPD],
]);

it('throws InvalidArgumentException from findByLegacyId() for an unknown legacy ID', function () {
    TestBootstrapper::forProposition(Proposition::MYPARCEL_ID);

    /** @var CarrierRepositoryInterface $repository */
    $repository = Pdk::get(CarrierRepositoryInterface::class);

    $repository->findByLegacyId(99999);
})->throws(InvalidArgumentException::class);

// exists()

it('returns true from exists() for a carrier present in the account', function () {
    TestBootstrapper::forProposition(Proposition::MYPARCEL_ID);

    /** @var CarrierRepositoryInterface $repository */
    $repository = Pdk::get(CarrierRepositoryInterface::class);

    expect($repository->exists(RefCapabilitiesSharedCarrierV2::POSTNL))->toBeTrue();
});

it('returns false from exists() for a carrier not present in the account', function () {
    TestBootstrapper::forProposition(Proposition::MYPARCEL_ID);

    /** @var CarrierRepositoryInterface $repository */
    $repository = Pdk::get(CarrierRepositoryInterface::class);

    expect($repository->exists('UNKNOWN_CARRIER_XYZ'))->toBeFalse();
});
