<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Order;

use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Tests\Factory\Collection\FactoryCollection;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

beforeEach(function () {
    (new FactoryCollection([
        factory(PdkOrder::class)
            ->withExternalIdentifier('bloemkool'),
        factory(PdkOrder::class)
            ->withExternalIdentifier('broccoli'),
        factory(PdkOrder::class)
            ->withExternalIdentifier('wortel'),
    ]))->store();
});

it('fetches single order', function () {
    $result  = Actions::execute(PdkBackendActions::FETCH_ORDERS, ['orderIds' => 'bloemkool']);
    $content = json_decode($result->getContent(), true);
    $orders  = Arr::get($content, 'data.orders');

    expect($orders)
        ->toHaveLength(1)
        ->and(Arr::pluck($orders, 'externalIdentifier'))
        ->toBe(['bloemkool']);
});

it('fetches multiple orders', function () {
    $result  = Actions::execute(PdkBackendActions::FETCH_ORDERS, ['orderIds' => ['bloemkool', 'broccoli']]);
    $content = json_decode($result->getContent(), true);
    $orders  = Arr::get($content, 'data.orders');

    expect($orders)
        ->toHaveLength(2)
        ->and(Arr::pluck($orders, 'externalIdentifier'))
        ->toBe(['bloemkool', 'broccoli']);
});

it('fetches multiple orders using semicolon separated ids', function () {
    $result  = Actions::execute(PdkBackendActions::FETCH_ORDERS, ['orderIds' => ['bloemkool;wortel', 'broccoli']]);
    $content = json_decode($result->getContent(), true);
    $orders  = Arr::get($content, 'data.orders');

    expect($orders)
        ->toHaveLength(3)
        ->and(Arr::pluck($orders, 'externalIdentifier'))
        ->toBe(['bloemkool', 'wortel', 'broccoli']);
});

it('handles empty order ids', function () {
    $result  = Actions::execute(PdkBackendActions::FETCH_ORDERS, ['orderIds' => []]);
    $content = json_decode($result->getContent(), true);
    $orders  = Arr::get($content, 'data.orders');

    expect($orders)->toHaveLength(0);
});

it('handles null order ids', function () {
    $result  = Actions::execute(PdkBackendActions::FETCH_ORDERS, ['orderIds' => [null, null, 'wortel']]);
    $content = json_decode($result->getContent(), true);
    $orders  = Arr::get($content, 'data.orders');

    expect($orders)
        ->toHaveLength(1)
        ->and(Arr::pluck($orders, 'externalIdentifier'))
        ->toBe(['wortel']);
});
