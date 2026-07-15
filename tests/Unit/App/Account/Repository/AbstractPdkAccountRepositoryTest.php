<?php

/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Account\Repository;

use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\Context\Context;
use MyParcelNL\Pdk\Context\Service\ContextService;
use MyParcelNL\Pdk\Facade\Notifications;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleErrorResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockApi;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\Pdk\Tests\Uses\UsesApiMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;

use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance(), new UsesApiMock());

beforeEach(function () {
    Notifications::clear();
});

it('returns null and adds a notification when fetching the account from the api fails', function () {
    TestBootstrapper::hasApiKey('test-api-key');

    MockApi::enqueue(new ExampleErrorResponse());

    /** @var \MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface $repository */
    $repository = Pdk::get(PdkAccountRepositoryInterface::class);

    $account = $repository->getAccount(true);

    expect($account)
        ->toBeNull()
        ->and(Notifications::all()->isNotEmpty())
        ->toBeTrue();
});

it('creates plugin settings view context when fetching the account fails', function () {
    TestBootstrapper::hasApiKey('test-api-key');

    MockApi::enqueue(new ExampleErrorResponse());

    /** @var \MyParcelNL\Pdk\Context\Service\ContextService $service */
    $service = Pdk::get(ContextService::class);

    $context = $service->createContexts([Context::ID_PLUGIN_SETTINGS_VIEW]);

    expect($context->pluginSettingsView->toArray())->toBe([]);
});
