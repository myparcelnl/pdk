<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Service;

use MyParcelNL\Pdk\Account\Platform;
use MyParcelNL\Pdk\Base\Concern\PdkInterface;
use MyParcelNL\Pdk\Base\Contract\CountryServiceInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('returns all languages', function () {
    /** @var \MyParcelNL\Pdk\Base\Contract\CountryServiceInterface $service */
    $service = Pdk::get(CountryServiceInterface::class);
    $all     = $service->getAll();

    expect($all)
        ->not->toBeEmpty()
        ->and($all)
        ->toBeArray();
});

it('gets shipping zone for country', function (string $country, string $expectedZone) {
    /** @var \MyParcelNL\Pdk\Base\Contract\CountryServiceInterface $service */
    $service = Pdk::get(CountryServiceInterface::class);
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
    /** @var \MyParcelNL\Pdk\Base\Contract\CountryServiceInterface $service */
    $service = Pdk::get(CountryServiceInterface::class);
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

it('can check if a country is the local country', function (string $platform, string $country, bool $isLocal) {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdk $pdk */
    $pdk = Pdk::get(PdkInterface::class);

    $previousPlatform = $pdk->get('platform');
    $pdk->set('platform', $platform);

    /** @var \MyParcelNL\Pdk\Base\Contract\CountryServiceInterface $service */
    $service = Pdk::get(CountryServiceInterface::class);
    $result  = $service->isLocalCountry($country);

    expect($result)->toBe($isLocal);

    $pdk->set('platform', $previousPlatform);
})->with([
    'myparcelnl, check NL' => [
        'platform' => Platform::MYPARCEL_NAME,
        'country'  => 'NL',
        'isLocal'  => true,
    ],
    'myparcelnl, check BE' => [
        'platform' => Platform::MYPARCEL_NAME,
        'country'  => 'BE',
        'isLocal'  => false,
    ],
    'myparcelnl, check DE' => [
        'platform' => Platform::MYPARCEL_NAME,
        'country'  => 'DE',
        'isLocal'  => false,
    ],
    'myparcelbe, check BE' => [
        'platform' => Platform::SENDMYPARCEL_NAME,
        'country'  => 'BE',
        'isLocal'  => true,
    ],
    'myparcelbe, check NL' => [
        'platform' => Platform::SENDMYPARCEL_NAME,
        'country'  => 'NL',
        'isLocal'  => false,
    ],
]);

it('can check if a country is in the EU zone', function (string $country, bool $isEu) {
    /** @var \MyParcelNL\Pdk\Base\Contract\CountryServiceInterface $service */
    $service = Pdk::get(CountryServiceInterface::class);
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
    /** @var \MyParcelNL\Pdk\Base\Contract\CountryServiceInterface $service */
    $service = Pdk::get(CountryServiceInterface::class);
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

it('gets countries with translation keys', function () {
    /** @var \MyParcelNL\Pdk\Base\Contract\CountryServiceInterface $service */
    $service = Pdk::get(CountryServiceInterface::class);
    $all     = $service->getAllTranslatable();

    expect(($all))->toHaveKeysAndValues([
        'NL' => 'country_nl',
    ]);
});
