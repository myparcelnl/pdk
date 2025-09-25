<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Model;

use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('returns true for isTest when in acceptance environment', function () {
    $settings = new AccountGeneralSettings();
    $settings->setEnvironment('acceptance');
    
    expect($settings->isTest)->toBeTrue();
});

it('returns false for isTest when in production environment', function () {
    $settings = new AccountGeneralSettings();
    $settings->setEnvironment('production');
    
    expect($settings->isTest)->toBeFalse();
});

it('returns correct environment values', function () {
    $settings = new AccountGeneralSettings();
    
    // Test default environment
    expect($settings->getEnvironment())->toBe('production');
    
    // Test setting acceptance environment
    $settings->setEnvironment('acceptance');
    expect($settings->getEnvironment())->toBe('acceptance');
    expect($settings->isAcceptance())->toBeTrue();
    expect($settings->isProduction())->toBeFalse();
    
    // Test setting production environment
    $settings->setEnvironment('production');
    expect($settings->getEnvironment())->toBe('production');
    expect($settings->isAcceptance())->toBeFalse();
    expect($settings->isProduction())->toBeTrue();
});

it('has correct default attributes', function () {
    $settings = new AccountGeneralSettings();
    
    expect($settings->attributes)->toBeArray()
        ->and($settings->attributes)->toHaveKey('environment')
        ->and($settings->attributes)->toHaveKey('orderMode')
        ->and($settings->attributes)->toHaveKey('hasCarrierContract')
        ->and($settings->attributes)->toHaveKey('hasCarrierSmallPackageContract')
        ->and($settings->attributes['environment'])->toBe('production')
        ->and($settings->attributes['orderMode'])->toBeFalse()
        ->and($settings->attributes['hasCarrierContract'])->toBeFalse()
        ->and($settings->attributes['hasCarrierSmallPackageContract'])->toBeFalse();
});

it('has correct casts', function () {
    $settings = new AccountGeneralSettings();
    
    expect($settings->casts)->toBeArray()
        ->and($settings->casts)->toHaveKey('environment')
        ->and($settings->casts)->toHaveKey('orderMode')
        ->and($settings->casts)->toHaveKey('hasCarrierContract')
        ->and($settings->casts)->toHaveKey('hasCarrierSmallPackageContract')
        ->and($settings->casts['environment'])->toBe('string')
        ->and($settings->casts['orderMode'])->toBe('bool')
        ->and($settings->casts['hasCarrierContract'])->toBe('bool')
        ->and($settings->casts['hasCarrierSmallPackageContract'])->toBe('bool');
});
