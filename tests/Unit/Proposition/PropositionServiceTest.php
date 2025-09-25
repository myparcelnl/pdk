<?php

/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Proposition;

use MyParcelNL\Pdk\Account\Platform;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Logger\Contract\PdkLoggerInterface;
use MyParcelNL\Pdk\Proposition\Model\PropositionConfig;
use MyParcelNL\Pdk\Proposition\Service\PropositionService;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use Psr\Log\LogLevel;

it('fetches proposition config for the active proposition', function (string $platform) {
    TestBootstrapper::forPlatform($platform);
    $propositionService = new PropositionService();
    $activeName             = $propositionService->getActivePropositionName();
    expect($activeName)->toBe($platform);
})->with('platforms');


it('handles flespakket as deprecated', function () {
    TestBootstrapper::forPlatform(Platform::FLESPAKKET_NAME);

    $logger = Pdk::get(PdkLoggerInterface::class);

    $propositionService = new PropositionService();

    // Verify flespakket deprecation
    expect($propositionService->getPropositionConfigByName(Platform::FLESPAKKET_NAME))->toNotBeNull();

    $lastLog = Arr::last($logger->getLogs());

    expect($lastLog['level'])
        ->toBe(LogLevel::NOTICE)
        ->and($lastLog['message'])
        ->toStartWith('[PDK]: [DEPRECATED] Flespakket platform is deprecated.');
});

it('throws exception for unknown proposition', function () {
    TestBootstrapper::forPlatform(Platform::SENDMYPARCEL_NAME);

    $propositionService = new PropositionService();

    $propositionService->getPropositionConfigByName('unknown_proposition_name');
})->throws(\InvalidArgumentException::class, 'Proposition config name unknown_proposition_name does not exist');

it('only fetches the config once per request', function () {
    TestBootstrapper::forPlatform(Platform::SENDMYPARCEL_NAME);

    $propositionService = new PropositionService();
    $propositionService->clearCache();

    $logger = Pdk::get(PdkLoggerInterface::class);

    $config1 = $propositionService->getPropositionConfigByName(Platform::SENDMYPARCEL_NAME);

    // Check debug log
    $lastLog = Arr::last($logger->getLogs());
    $logCount = count($logger->getLogs());

    expect($lastLog['level'])
        ->toBe(LogLevel::DEBUG)
        ->and($lastLog['message'])
        ->toBe('[PDK]: Proposition config loaded from source.');

    $config2 = $propositionService->getPropositionConfigByName(Platform::SENDMYPARCEL_NAME);
    $logCount2 = count($logger->getLogs());

    expect($logCount2)->toBe($logCount);
    expect($config1)->toBe($config2)->and($config1)->toBeInstanceOf(PropositionConfig::class);
});

it('clears specific proposition caches', function () {
    TestBootstrapper::forPlatform(Platform::SENDMYPARCEL_NAME);

    $propositionService = new PropositionService();
    $propositionService->clearCache();

    $logger = Pdk::get(PdkLoggerInterface::class);

    $config1 = $propositionService->getPropositionConfigByName(Platform::SENDMYPARCEL_NAME);

    // Check debug log
    $lastLog = Arr::last($logger->getLogs());
    $logCount = count($logger->getLogs());

    expect($lastLog['level'])
        ->toBe(LogLevel::DEBUG)
        ->and($lastLog['message'])
        ->toBe('[PDK]: Proposition config loaded from source.');

    // Verify fetched from cache
    $config2 = $propositionService->getPropositionConfigByName(Platform::SENDMYPARCEL_NAME);
    expect($propositionService->isCached(Platform::SENDMYPARCEL_NAME))->toBeTrue();
    $logCount2 = count($logger->getLogs());

    expect($logCount2)->toBe($logCount);
    expect($config1)->toBe($config2)->and($config1)->toBeInstanceOf(PropositionConfig::class);

    // Clear only the SendMyParcel proposition cache
    $propositionService->clearCache(Platform::SENDMYPARCEL_NAME);
    expect($propositionService->isCached(Platform::SENDMYPARCEL_NAME))->toBeFalse();

    $config3 = $propositionService->getPropositionConfigByName(Platform::SENDMYPARCEL_NAME);
    $logCount3 = count($logger->getLogs());

    // New log entry should be added
    expect($logCount3)->toBeGreaterThan($logCount2);

    $lastLog = Arr::last($logger->getLogs());
    expect($lastLog['level'])
        ->toBe(LogLevel::DEBUG)
        ->and($lastLog['message'])
        ->toBe('[PDK]: Proposition config loaded from source.');

    expect($config3)->toBeInstanceOf(PropositionConfig::class);
    expect($config3)->not->toBe($config1)->and($config3)->not->toBe($config2);
});


it('returns carriers', function () {
    TestBootstrapper::forPlatform(Platform::SENDMYPARCEL_NAME);

    $propositionService = new PropositionService();

    $carriers = $propositionService->getCarriers();

    expect($carriers)->toBeInstanceOf(CarrierCollection::class);
});

it('can return legacy carrier names', function () {
    TestBootstrapper::forPlatform(Platform::SENDMYPARCEL_NAME);

    $propositionService = new PropositionService();

    $name = $propositionService->mapNewToLegacyCarrierName('DHL_FOR_YOU');

    expect($name)->toBe('dhlforyou');
});

it('can filter out carriers with unsupported package types', function () {
    TestBootstrapper::forPlatform(Platform::MYPARCEL_NAME);

    $propositionService = new PropositionService();

    $allCarriers = $propositionService->getCarriers(false)->toArray();
    expect(array_column($allCarriers, 'name'))->toContain(Carrier::CARRIER_CHEAP_CARGO_NAME);

    $carriers = $propositionService->getCarriers(true)->toArray();

    expect(count($allCarriers))->toBeGreaterThan(count($carriers));
    expect($carriers)->not->toBeEmpty();
    expect(array_column($carriers, 'name'))->not->toContain(Carrier::CARRIER_CHEAP_CARGO_NAME);
});
