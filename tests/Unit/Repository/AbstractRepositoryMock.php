<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Account\Repository\AbstractAccountRepository;
use MyParcelNL\Pdk\Api\Service\AbstractApiService;
use MyParcelNL\Pdk\Api\Service\ApiServiceInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Bootstrap\MockRepository;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Sdk\src\Model\Account\Shop;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('retrieves api instance', function () {
    /** @var \MyParcelNL\Pdk\Api\Service\ApiServiceInterface $api */
    $api = Pdk::get(ApiServiceInterface::class);

    expect($api)->toBeInstanceOf(AbstractApiService::class);
});

it('handles repository', function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockRepository $repository */
    $repository = Pdk::get(MockRepository::class);

    expect($repository->getShopWithParameters(1))->toBeInstanceOf(Shop::class);
});

it('gets data from the api', function () {
    /** @var \MyParcelNL\Pdk\Account\Repository\AccountRepositoryInterface $accountRepository */
    $accountRepository = Pdk::get(AbstractAccountRepository::class);

    expect(
        $accountRepository
            ->getAccount()
            ->toArray()
    )->toBeArray();
});
