<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Account\Repository\AccountRepository;
use MyParcelNL\Pdk\Api\Service\AbstractApiService;
use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use MyParcelNL\Pdk\Tests\Bootstrap\MockRepository;
use MyParcelNL\Sdk\src\Model\Account\Shop;

/** @var \MyParcelNL\Pdk\Base\Pdk $pdk */
$pdk = null;

beforeEach(function () use (&$pdk) {
    $pdk = PdkFactory::create(MockPdkConfig::create());
});

it('sets up api', function () use ($pdk) {
    /** @var \MyParcelNL\Pdk\Api\Service\ApiServiceInterface $api */
    $api = Pdk::get('api');

    expect($api)->toBeInstanceOf(AbstractApiService::class);
});

it('handles repository', function () use ($pdk) {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockRepository $repository */
    $repository = Pdk::get(MockRepository::class);

    expect($repository->getShopWithParameters(1))->toBeInstanceOf(Shop::class);
});

it('apis the api', function () use ($pdk) {
    /** @var \MyParcelNL\Pdk\Account\Repository\AccountRepository $accountRepository */
    $accountRepository = Pdk::get(AccountRepository::class);

    expect(
        $accountRepository
            ->getAccount()
            ->toArray()
    )->toBeArray();
});
