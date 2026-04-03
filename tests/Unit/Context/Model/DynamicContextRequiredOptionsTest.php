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

it('dynamic context carrier settings keeps INHERIT for non-required options', function () {
    factory(Carrier::class)
        ->withAllCapabilities()
        ->store();

    $storage = Pdk::get(StorageInterface::class);
    $storage->delete('carrier:POSTNL');
    $storage->delete('carrier:all');

    factory(Settings::class)
        ->withCarrier('POSTNL')
        ->store();

    $context  = new DynamicContext();
    $settings = $context->pluginSettings->carrier->firstWhere('id', 'POSTNL');

    expect($settings)->not->toBeNull()
        ->and($settings->exportSignature)->toBe(TriStateService::INHERIT);
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
