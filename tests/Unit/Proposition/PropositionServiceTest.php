<?php

/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Proposition;

use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Logger\Contract\PdkLoggerInterface;
use MyParcelNL\Pdk\Proposition\Model\PropositionConfig;
use MyParcelNL\Pdk\Proposition\Service\PropositionService;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use Psr\Log\LogLevel;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefTypesCarrier;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2;

it('fetches proposition config for the active proposition', function (int $propositionId, string $expectedKey) {
    TestBootstrapper::forProposition($propositionId);
    $propositionService = Pdk::get(PropositionService::class);
    $propositionName = $propositionService->getPropositionConfig()->proposition->key;
    expect($propositionName)->toBe($expectedKey);
})->with([
    [Proposition::MYPARCEL_ID, Proposition::MYPARCEL_NAME],
    [Proposition::SENDMYPARCEL_ID, Proposition::SENDMYPARCEL_NAME],
]);

it('throws exception for unknown proposition', function () {
    TestBootstrapper::forProposition(Proposition::SENDMYPARCEL_ID);

    $propositionService = Pdk::get(PropositionService::class);

    $propositionService->getPropositionConfigById(9999);
})->throws(\InvalidArgumentException::class, 'Proposition config ID 9999 does not exist');


it('handles empty files', function () {
    TestBootstrapper::forProposition(Proposition::MYPARCEL_ID);

    $logger = Pdk::get(PdkLoggerInterface::class);

    $propositionService = Pdk::get(PropositionService::class);

    $propositionService->processConfigData(1337, 'mock-path', '');
})->throws(\RuntimeException::class, 'Proposition config file: mock-path appears to be empty');


it('handles invalid json', function () {
    TestBootstrapper::forProposition(Proposition::MYPARCEL_ID);

    $logger = Pdk::get(PdkLoggerInterface::class);

    $propositionService = Pdk::get(PropositionService::class);

    $propositionService->processConfigData(1223, 'mock-path', '{ invalid json }');
})->throws(\RuntimeException::class, 'Invalid JSON in proposition config file: mock-path - Error: Syntax error');

it('only fetches the config once per request', function () {
    TestBootstrapper::forProposition(Proposition::SENDMYPARCEL_ID);

    $propositionService = Pdk::get(PropositionService::class);
    $propositionService->clearCache();

    $logger = Pdk::get(PdkLoggerInterface::class);

    $config1 = $propositionService->getPropositionConfigById(Proposition::SENDMYPARCEL_ID);

    // Check debug log
    $lastLog = Arr::last($logger->getLogs());
    $logCount = count($logger->getLogs());

    expect($lastLog['level'])
        ->toBe(LogLevel::DEBUG)
        ->and($lastLog['message'])
        ->toBe('[PDK]: Proposition config loaded from source.');

    $config2 = $propositionService->getPropositionConfigById(Proposition::SENDMYPARCEL_ID);
    $logCount2 = count($logger->getLogs());

    expect($logCount2)->toBe($logCount);
    expect($config1)->toBe($config2)->and($config1)->toBeInstanceOf(PropositionConfig::class);
});

it('clears specific proposition caches', function () {
    TestBootstrapper::forProposition(Proposition::SENDMYPARCEL_ID);

    $propositionService = Pdk::get(PropositionService::class);
    $propositionService->clearCache();

    $logger = Pdk::get(PdkLoggerInterface::class);

    $config1 = $propositionService->getPropositionConfigById(Proposition::SENDMYPARCEL_ID);

    // Check debug log
    $lastLog = Arr::last($logger->getLogs());
    $logCount = count($logger->getLogs());

    expect($lastLog['level'])
        ->toBe(LogLevel::DEBUG)
        ->and($lastLog['message'])
        ->toBe('[PDK]: Proposition config loaded from source.');

    // Verify fetched from cache
    $config2 = $propositionService->getPropositionConfigById(Proposition::SENDMYPARCEL_ID);
    expect($propositionService->isCached(Proposition::SENDMYPARCEL_ID))->toBeTrue();
    $logCount2 = count($logger->getLogs());

    expect($logCount2)->toBe($logCount);
    expect($config1)->toBe($config2)->and($config1)->toBeInstanceOf(PropositionConfig::class);

    // Clear only the SendMyParcel proposition cache
    $propositionService->clearCache(Proposition::SENDMYPARCEL_ID);
    expect($propositionService->isCached(Proposition::SENDMYPARCEL_ID))->toBeFalse();

    $config3 = $propositionService->getPropositionConfigById(Proposition::SENDMYPARCEL_ID);
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
