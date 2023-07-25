<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Repository;

use MyParcelNL\Pdk\Account\Repository\AccountRepository;
use MyParcelNL\Pdk\Api\Contract\ApiServiceInterface;
use MyParcelNL\Pdk\Api\Service\AbstractApiService;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetAccountsResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockApi;
use MyParcelNL\Pdk\Tests\Uses\UsesApiMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance(), new UsesApiMock());

it('retrieves api instance', function () {
    /** @var \MyParcelNL\Pdk\Api\Contract\ApiServiceInterface $api */
    $api = Pdk::get(ApiServiceInterface::class);

    expect($api)->toBeInstanceOf(AbstractApiService::class);
});

it('gets data from the api', function () {
    MockApi::enqueue(new ExampleGetAccountsResponse());

    /** @var \MyParcelNL\Pdk\Account\Repository\AccountRepository $accountRepository */
    $accountRepository = Pdk::get(AccountRepository::class);

    $account = $accountRepository->getAccount();

    expect($account->toArray())->toBeArray();
});
