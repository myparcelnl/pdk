<?php

/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Context\Model;

use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\Settings;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

uses()->group('context', 'carrier-settings');

usesShared(new UsesMockPdkInstance(), new UsesAccountMock());

it('dynamic context carrier settings has ENABLED for isRequired options', function () {
    factory(Carrier::class)
        ->withAllCapabilities()
        ->withOptionRequired('requiresSignature')
        ->store();

    $storage = Pdk::get(StorageInterface::class);
    $storage->delete('carrier:POSTNL');
    $storage->delete('carrier:all');

    // Store default carrier settings — exportSignature defaults to INHERIT (-1)
    factory(Settings::class)
        ->withCarrier('POSTNL')
        ->store();

    $context  = new DynamicContext();
    $settings = $context->pluginSettings->carrier->firstWhere('id', 'POSTNL');

    expect($settings)->not->toBeNull()
        ->and($settings->exportSignature)->toBe(TriStateService::ENABLED);
});

it('dynamic context resolves INHERIT to DISABLED for non-required options', function () {
    factory(Carrier::class)
        ->withAllCapabilities()
        ->store();

    $storage = Pdk::get(StorageInterface::class);
    $storage->delete('carrier:POSTNL');
    $storage->delete('carrier:all');

    // exportSignature defaults to INHERIT (-1) in stored settings
    factory(Settings::class)
        ->withCarrier('POSTNL')
        ->store();

    $context  = new DynamicContext();
    $settings = $context->pluginSettings->carrier->firstWhere('id', 'POSTNL');

    // INHERIT is resolved at display time: isSelectedByDefault is false → DISABLED
    expect($settings)->not->toBeNull()
        ->and($settings->exportSignature)->toBe(TriStateService::DISABLED);
});

it('dynamic context enforces ENABLED for carrier without saved settings', function () {
    factory(Carrier::class)
        ->withAllCapabilities('DHL_PARCEL_CONNECT')
        ->withOptionRequired('requiresSignature')
        ->store();

    $storage = Pdk::get(StorageInterface::class);
    $storage->delete('carrier:DHL_PARCEL_CONNECT');
    $storage->delete('carrier:all');

    // Only store settings for POSTNL — DHL_PARCEL_CONNECT has no saved settings
    factory(Settings::class)
        ->withCarrier('POSTNL')
        ->store();

    $context  = new DynamicContext();
    $settings = $context->pluginSettings->carrier->firstWhere('id', 'DHL_PARCEL_CONNECT');

    expect($settings)->not->toBeNull()
        ->and($settings->exportSignature)->toBe(TriStateService::ENABLED);
});

it('dynamic context applies isSelectedByDefault for carrier without saved settings', function () {
    factory(Carrier::class)
        ->withAllCapabilities('DHL_PARCEL_CONNECT')
        ->withOptionSelectedByDefault('requiresSignature')
        ->store();

    $storage = Pdk::get(StorageInterface::class);
    $storage->delete('carrier:DHL_PARCEL_CONNECT');
    $storage->delete('carrier:all');

    // Only store settings for POSTNL — DHL_PARCEL_CONNECT has no saved settings
    factory(Settings::class)
        ->withCarrier('POSTNL')
        ->store();

    $context  = new DynamicContext();
    $settings = $context->pluginSettings->carrier->firstWhere('id', 'DHL_PARCEL_CONNECT');

    expect($settings)->not->toBeNull()
        ->and($settings->exportSignature)->toBe(TriStateService::ENABLED);
});

it('dynamic context resolves INHERIT to ENABLED via isSelectedByDefault on saved carrier', function () {
    factory(Carrier::class)
        ->withAllCapabilities()
        ->withOptionSelectedByDefault('requiresSignature')
        ->store();

    $storage = Pdk::get(StorageInterface::class);
    $storage->delete('carrier:POSTNL');
    $storage->delete('carrier:all');

    // Carrier has saved settings but exportSignature is INHERIT (default)
    factory(Settings::class)
        ->withCarrier('POSTNL')
        ->store();

    $context  = new DynamicContext();
    $settings = $context->pluginSettings->carrier->firstWhere('id', 'POSTNL');

    // INHERIT is resolved at display time against current capabilities
    expect($settings)->not->toBeNull()
        ->and($settings->exportSignature)->toBe(TriStateService::ENABLED);
});

it('dynamic context does not apply isSelectedByDefault for carrier with saved settings', function () {
    factory(Carrier::class)
        ->withAllCapabilities()
        ->withOptionSelectedByDefault('requiresSignature')
        ->store();

    $storage = Pdk::get(StorageInterface::class);
    $storage->delete('carrier:POSTNL');
    $storage->delete('carrier:all');

    // Carrier has saved settings with signature explicitly DISABLED
    factory(Settings::class)
        ->withCarrier('POSTNL', [CarrierSettings::EXPORT_SIGNATURE => TriStateService::DISABLED])
        ->store();

    $context  = new DynamicContext();
    $settings = $context->pluginSettings->carrier->firstWhere('id', 'POSTNL');

    // isSelectedByDefault should NOT override explicitly saved settings
    expect($settings)->not->toBeNull()
        ->and($settings->exportSignature)->toBe(TriStateService::DISABLED);
});

it('dynamic context carrier settings forces ENABLED even when explicitly DISABLED for isRequired', function () {
    factory(Carrier::class)
        ->withAllCapabilities()
        ->withOptionRequired('requiresSignature')
        ->store();

    $storage = Pdk::get(StorageInterface::class);
    $storage->delete('carrier:POSTNL');
    $storage->delete('carrier:all');

    // Explicitly DISABLE signature
    factory(Settings::class)
        ->withCarrier('POSTNL', [CarrierSettings::EXPORT_SIGNATURE => 0])
        ->store();

    $context  = new DynamicContext();
    $settings = $context->pluginSettings->carrier->firstWhere('id', 'POSTNL');

    expect($settings)->not->toBeNull()
        ->and($settings->exportSignature)->toBe(TriStateService::ENABLED);
});
