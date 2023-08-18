<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Repository;

use MyParcelNL\Pdk\Account\Model\Shop;
use MyParcelNL\Pdk\Base\Facade\MockApi;
use MyParcelNL\Pdk\Base\Repository\MockApiRepository;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Mock\Api\Response\ExampleGetShopsResponse;

it('gets shop', function () {
    MockApi::enqueue(new ExampleGetShopsResponse());

    /** @var \MyParcelNL\Pdk\Base\Repository\MockApiRepository $repository */
    $repository = Pdk::get(MockApiRepository::class);

    expect($repository->getShopWithParameters(1))->toBeInstanceOf(Shop::class);
});
