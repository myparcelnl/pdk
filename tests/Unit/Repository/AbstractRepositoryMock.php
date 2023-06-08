<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Api\Contract\ApiServiceInterface;
use MyParcelNL\Pdk\Api\Service\AbstractApiService;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetAccountsResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockAccountRepository;
use MyParcelNL\Pdk\Tests\Bootstrap\MockRepository;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Sdk\src\Model\Account\Shop;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('retrieves api instance', function () {
    /** @var \MyParcelNL\Pdk\Api\Contract\ApiServiceInterface $api */
    $api = Pdk::get(ApiServiceInterface::class);

    expect($api)->toBeInstanceOf(AbstractApiService::class);
});

it('handles repository', function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockRepository $repository */
    $repository = Pdk::get(MockRepository::class);

    expect($repository->getShopWithParameters(1))->toBeInstanceOf(Shop::class);
});

it('gets data from the api', function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api = Pdk::get(ApiServiceInterface::class);

    $api->getMock()
        ->append(new ExampleGetAccountsResponse());

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockAccountRepository $accountRepository */
    $accountRepository = Pdk::get(MockAccountRepository::class);

    $account = $accountRepository->getAccount();

    expect($account->toArray())->toBeArray();
});
