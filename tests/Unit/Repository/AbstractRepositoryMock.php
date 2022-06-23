<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Account\Repository\AccountRepository;
use MyParcelNL\Pdk\Api\MyParcelApiService;
use MyParcelNL\Pdk\Api\Service\AbstractApiService;
use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Tests\Bootstrap\MockApiService;
use MyParcelNL\Pdk\Tests\Bootstrap\MockRepository;
use MyParcelNL\Pdk\Tests\Bootstrap\MockStorage;
use MyParcelNL\Sdk\src\Model\Account\Shop;

$pdk = PdkFactory::createPdk([
    'storage' => [
        'default' => new MockStorage(),
    ],
    'api'     => new MockApiService(),
]);

it('sets up api', function () use ($pdk) {
    /** @var MyParcelApiService $api */
    $api     = $pdk->get('api');
    $baseUrl = $api->getBaseUrl();

    expect($api)->toBeInstanceOf(AbstractApiService::class);
});

it('handles repository', function () use ($pdk) {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockRepository $repository */
    $repository = $pdk->get(MockRepository::class);

    expect($repository->getShopWithParameters())->toBeInstanceOf(Shop::class);
});

it('apis the api', function () use ($pdk) {
    /** @var \MyParcelNL\Pdk\Account\Repository\AccountRepository $accountRepository */
    $accountRepository = $pdk->get(AccountRepository::class);

    expect(
        $accountRepository
            ->getAccount()
            ->toArray()
    )->toBeArray();
});
