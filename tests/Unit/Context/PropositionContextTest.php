<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Context;

use MyParcelNL\Pdk\Account\Platform;
use MyParcelNL\Pdk\Context\Model\DeliveryOptionsConfig;
use MyParcelNL\Pdk\Context\Model\GlobalContext;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkFactory;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

/**
 * Test suite for Context Models with Proposition properties
 * 
 * These tests ensure:
 * 1. New 'proposition' properties work correctly
 * 2. Legacy 'platform' properties remain functional
 * 3. Both properties contain identical data (parity)
 */

// GlobalContext Tests

it('GlobalContext has both proposition and platform properties', function () {
    MockPdkFactory::create(['platform' => Platform::MYPARCEL_NAME]);
    
    $context = new GlobalContext();
    $array = $context->toArray();

    expect($array)->toHaveKey('proposition')
        ->and($array)->toHaveKey('platform');
});

it('GlobalContext proposition and platform properties contain same data', function () {
    MockPdkFactory::create(['platform' => Platform::MYPARCEL_NAME]);
    
    $context = new GlobalContext();

    expect($context->proposition)->toBe($context->platform);
});

it('GlobalContext proposition property contains expected keys', function () {
    MockPdkFactory::create(['platform' => Platform::MYPARCEL_NAME]);
    
    $context = new GlobalContext();

    expect($context->proposition)->toBeArray()
        ->and($context->proposition)->toHaveKeys([
            'name',
            'human',
            'backofficeUrl',
            'supportUrl',
            'localCountry',
            'defaultCarrier',
            'defaultCarrierId',
        ]);
});

it('GlobalContext platform property contains same keys as proposition', function () {
    MockPdkFactory::create(['platform' => Platform::MYPARCEL_NAME]);
    
    $context = new GlobalContext();

    $propositionKeys = array_keys($context->proposition);
    $platformKeys = array_keys($context->platform);

    expect($platformKeys)->toBe($propositionKeys);
});

it('GlobalContext proposition data matches platform data for MyParcel', function () {
    MockPdkFactory::create(['platform' => Platform::MYPARCEL_NAME]);
    
    $context = new GlobalContext();

    expect($context->proposition['name'])->toBe('myparcel')
        ->and($context->proposition['localCountry'])->toBe('NL')
        ->and($context->platform)->toBe($context->proposition);
});

it('GlobalContext proposition data matches platform data for SendMyParcel', function () {
    MockPdkFactory::create(['platform' => Platform::SENDMYPARCEL_NAME]);
    
    $context = new GlobalContext();

    expect($context->proposition['name'])->toBe('belgie')
        ->and($context->proposition['localCountry'])->toBe('BE')
        ->and($context->platform)->toBe($context->proposition);
});

// DeliveryOptionsConfig Tests

it('DeliveryOptionsConfig has both proposition and platform properties', function () {
    MockPdkFactory::create(['platform' => Platform::MYPARCEL_NAME]);
    
    $config = new DeliveryOptionsConfig();
    $array = $config->toArray();

    expect($array)->toHaveKey('proposition')
        ->and($array)->toHaveKey('platform');
});

it('DeliveryOptionsConfig proposition and platform properties contain same value', function () {
    MockPdkFactory::create(['platform' => Platform::MYPARCEL_NAME]);
    
    $config = new DeliveryOptionsConfig();

    expect($config->proposition)->toBe($config->platform);
});

it('DeliveryOptionsConfig proposition property contains proposition name', function () {
    MockPdkFactory::create(['platform' => Platform::MYPARCEL_NAME]);
    
    $config = new DeliveryOptionsConfig();

    expect($config->proposition)->toBe('myparcel')
        ->and($config->platform)->toBe('myparcel');
});

it('DeliveryOptionsConfig updates correctly for different propositions', function (string $platform, string $expectedName) {
    MockPdkFactory::create(['platform' => $platform]);
    
    $config = new DeliveryOptionsConfig();

    expect($config->proposition)->toBe($expectedName)
        ->and($config->platform)->toBe($expectedName);
})->with([
    'MyParcel' => [Platform::MYPARCEL_NAME, 'myparcel'],
    'SendMyParcel' => [Platform::SENDMYPARCEL_NAME, 'belgie'],
]);

it('DeliveryOptionsConfig fromCart method preserves both properties', function () {
    MockPdkFactory::create(['platform' => Platform::MYPARCEL_NAME]);
    
    $cart = \MyParcelNL\Pdk\Tests\factory(\MyParcelNL\Pdk\App\Cart\Model\PdkCart::class)->make();
    
    $config = DeliveryOptionsConfig::fromCart($cart);

    expect($config->proposition)->not->toBeNull()
        ->and($config->platform)->not->toBeNull()
        ->and($config->proposition)->toBe($config->platform);
});

/**
 * SERIALIZATION TESTS
 * 
 * Ensure both properties are serialized correctly
 */

it('serializes both proposition and platform in GlobalContext', function () {
    MockPdkFactory::create(['platform' => Platform::MYPARCEL_NAME]);
    
    $context = new GlobalContext();
    $array = $context->toArray();

    expect($array)->toHaveKey('proposition')
        ->and($array)->toHaveKey('platform')
        ->and($array['proposition'])->toBe($array['platform']);
});

it('serializes both proposition and platform in DeliveryOptionsConfig', function () {
    MockPdkFactory::create(['platform' => Platform::MYPARCEL_NAME]);
    
    $config = new DeliveryOptionsConfig();
    $array = $config->toArray();

    expect($array)->toHaveKey('proposition')
        ->and($array)->toHaveKey('platform')
        ->and($array['proposition'])->toBe($array['platform']);
});
