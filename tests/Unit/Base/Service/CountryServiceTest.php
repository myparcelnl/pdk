<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Base\Service\CountryService;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;

beforeEach(function () {
    PdkFactory::create(MockPdkConfig::create());
});

it('returns all languages', function () {
    /** @var \MyParcelNL\Pdk\Base\Service\CountryService $service */
    $service = Pdk::get(CountryService::class);
    $all     = $service->getAll();

    expect($all)
        ->not->toBeEmpty()
        ->and($all)
        ->toBeArray();
});

it('gets shipping zone for country', function (string $country, string $expectedZone) {
    /** @var \MyParcelNL\Pdk\Base\Service\CountryService $service */
    $service = Pdk::get(CountryService::class);
    $zone    = $service->getShippingZone($country);

    expect($zone)->toBe($expectedZone);
})->with([
    'NL' => [
        'country'      => 'NL',
        'expectedZone' => 'NL',
    ],
    'BE' => [
        'country'      => 'BE',
        'expectedZone' => 'BE',
    ],
    'DE' => [
        'country'      => 'DE',
        'expectedZone' => 'EU',
    ],
    'US' => [
        'country'      => 'US',
        'expectedZone' => 'ROW',
    ],
]);

it('can check if a country is an unique zone', function (string $country, bool $unique) {
    /** @var \MyParcelNL\Pdk\Base\Service\CountryService $service */
    $service = Pdk::get(CountryService::class);
    $result  = $service->isUnique($country);

    expect($result)->toBe($unique);
})->with([
    'NL' => [
        'country' => 'NL',
        'unique'  => true,
    ],
    'BE' => [
        'country' => 'BE',
        'unique'  => true,
    ],
    'DE' => [
        'country' => 'DE',
        'unique'  => false,
    ],
    'US' => [
        'country' => 'US',
        'unique'  => false,
    ],
]);

it('can check if a country is in the EU zone', function (string $country, bool $isEu) {
    /** @var \MyParcelNL\Pdk\Base\Service\CountryService $service */
    $service = Pdk::get(CountryService::class);
    $result  = $service->isEu($country);

    expect($result)->toBe($isEu);
})->with([
    'NL' => [
        'country' => 'NL',
        'isEu'    => false,
    ],
    'BE' => [
        'country' => 'BE',
        'isEu'    => false,
    ],
    'DE' => [
        'country' => 'DE',
        'isEu'    => true,
    ],
    'US' => [
        'country' => 'US',
        'isEu'    => false,
    ],
]);

it('can check if a country is in the ROW zone', function (string $country, bool $isRow) {
    /** @var \MyParcelNL\Pdk\Base\Service\CountryService $service */
    $service = Pdk::get(CountryService::class);
    $result  = $service->isRow($country);

    expect($result)->toBe($isRow);
})->with([
    'NL' => [
        'country' => 'NL',
        'isRow'   => false,
    ],
    'BE' => [
        'country' => 'BE',
        'isRow'   => false,
    ],
    'DE' => [
        'country' => 'DE',
        'isRow'   => false,
    ],
    'US' => [
        'country' => 'US',
        'isRow'   => true,
    ],
    'XX' => [
        'country' => 'XX',
        'isRow'   => true,
    ],
]);
