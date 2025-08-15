<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Model;

use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

beforeEach(function () {
    // Clean up any existing cache file
    $cacheFile = sys_get_temp_dir() . '/pdk_acceptance_api_url.txt';
    if (file_exists($cacheFile)) {
        unlink($cacheFile);
    }
});

afterEach(function () {
    // Clean up cache file after each test
    $cacheFile = sys_get_temp_dir() . '/pdk_acceptance_api_url.txt';
    if (file_exists($cacheFile)) {
        unlink($cacheFile);
    }
});

it('returns true for isTest when connected to acceptance environment', function () {
    // Create acceptance cache file to simulate acceptance environment
    $cacheFile = sys_get_temp_dir() . '/pdk_acceptance_api_url.txt';
    file_put_contents($cacheFile, 'https://api.acceptance.myparcel.nl');
    
    $settings = new AccountGeneralSettings();
    
    expect($settings->isTest)->toBeTrue();
});

it('returns false for isTest when connected to production environment', function () {
    // Ensure no cache file exists to simulate production environment
    $cacheFile = sys_get_temp_dir() . '/pdk_acceptance_api_url.txt';
    if (file_exists($cacheFile)) {
        unlink($cacheFile);
    }
    
    $settings = new AccountGeneralSettings();
    
    expect($settings->isTest)->toBeFalse();
});

it('has correct default attributes', function () {
    $settings = new AccountGeneralSettings();
    
    expect($settings->attributes)->toBeArray()
        ->and($settings->attributes)->toHaveKey('isTest')
        ->and($settings->attributes)->toHaveKey('orderMode')
        ->and($settings->attributes)->toHaveKey('hasCarrierContract')
        ->and($settings->attributes)->toHaveKey('hasCarrierSmallPackageContract')
        ->and($settings->attributes['isTest'])->toBeNull()
        ->and($settings->attributes['orderMode'])->toBeFalse()
        ->and($settings->attributes['hasCarrierContract'])->toBeFalse()
        ->and($settings->attributes['hasCarrierSmallPackageContract'])->toBeFalse();
});

it('has correct casts', function () {
    $settings = new AccountGeneralSettings();
    
    expect($settings->casts)->toBeArray()
        ->and($settings->casts)->toHaveKey('isTest')
        ->and($settings->casts)->toHaveKey('orderMode')
        ->and($settings->casts)->toHaveKey('hasCarrierContract')
        ->and($settings->casts)->toHaveKey('hasCarrierSmallPackageContract')
        ->and($settings->casts['isTest'])->toBe('bool')
        ->and($settings->casts['orderMode'])->toBe('bool')
        ->and($settings->casts['hasCarrierContract'])->toBe('bool')
        ->and($settings->casts['hasCarrierSmallPackageContract'])->toBe('bool');
});
