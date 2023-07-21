<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Repository;

use MyParcelNL\Pdk\Account\Model\Shop;
use MyParcelNL\Pdk\Api\Contract\ApiServiceInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetShopsResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockRepository;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('gets shop', function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api = Pdk::get(ApiServiceInterface::class);

    $api->getMock()
        ->append(new ExampleGetShopsResponse());

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockRepository $repository */
    $repository = Pdk::get(MockRepository::class);

    expect($repository->getShopWithParameters(1))->toBeInstanceOf(Shop::class);
});
