<?php

/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Model;

use Mockery;
use MyParcelNL\Pdk\Base\Concern\PdkInterface;
use MyParcelNL\Pdk\Carrier\Contract\CarrierRepositoryInterface;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdk;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;

use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

// Test 1: defaultCarrier defaults to null on a fresh Shop
it('has a null defaultCarrier on a fresh Shop', function () {
    $shop = new Shop();

    expect($shop->defaultCarrier)->toBeNull();
});

// Test 2: defaultCarrierModel short-circuits on empty string without calling the repository
it('returns null for defaultCarrierModel when defaultCarrier is an empty string', function () {
    $carrierRepository = Mockery::mock(CarrierRepositoryInterface::class);
    $carrierRepository->shouldNotReceive('find');

    /** @var MockPdk $pdk */
    $pdk = Pdk::get(PdkInterface::class);
    $pdk->set(CarrierRepositoryInterface::class, $carrierRepository);

    $shop = new Shop(['defaultCarrier' => '']);

    expect($shop->defaultCarrierModel)->toBeNull();
});

// Test 3: defaultCarrierModel resolves to the Carrier returned by CarrierRepositoryInterface::find
it('resolves defaultCarrierModel via CarrierRepositoryInterface::find when defaultCarrier is set', function () {
    $carrier = new Carrier(['carrier' => 'POSTNL']);

    $carrierRepository = Mockery::mock(CarrierRepositoryInterface::class);
    $carrierRepository->shouldReceive('find')
        ->once()
        ->with('POSTNL')
        ->andReturn($carrier);

    /** @var MockPdk $pdk */
    $pdk = Pdk::get(PdkInterface::class);
    $pdk->set(CarrierRepositoryInterface::class, $carrierRepository);

    $shop = new Shop(['defaultCarrier' => 'POSTNL']);

    $resolved = $shop->defaultCarrierModel;

    expect($resolved)->toBeInstanceOf(Carrier::class)
        ->and($resolved->carrier)->toBe('POSTNL');
});

// Test 4: defaultCarrierModel returns null when the repository returns null for an unknown V2 name
it('returns null for defaultCarrierModel when the repository returns null for an unknown carrier name', function () {
    $carrierRepository = Mockery::mock(CarrierRepositoryInterface::class);
    $carrierRepository->shouldReceive('find')
        ->once()
        ->with('UNKNOWN_CARRIER')
        ->andReturn(null);

    /** @var MockPdk $pdk */
    $pdk = Pdk::get(PdkInterface::class);
    $pdk->set(CarrierRepositoryInterface::class, $carrierRepository);

    $shop = new Shop(['defaultCarrier' => 'UNKNOWN_CARRIER']);

    expect($shop->defaultCarrierModel)->toBeNull();
});

// Test 5: V2 string round-trips through toArray() and fill()
it('round-trips defaultCarrier through toArray() and fill()', function () {
    $original = new Shop(['defaultCarrier' => 'POSTNL']);

    $roundTripped = new Shop();
    $roundTripped->fill($original->toArray());

    expect($roundTripped->defaultCarrier)->toBe('POSTNL');
});
