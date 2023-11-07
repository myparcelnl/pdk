<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Model;

use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('returns product weight', function () {
    $orderLine = factory(PdkOrderLine::class)
        ->withQuantity(2)
        ->withProduct(factory(PdkProduct::class)->withWeight(1000))
        ->make();

    expect($orderLine->getTotalWeight())->toBe(2000);
});

it('returns weight 0 if no product is set', function () {
    $orderLine = factory(PdkOrderLine::class)
        ->fromScratch()
        ->make();

    expect($orderLine->getTotalWeight())->toBe(0);
});
