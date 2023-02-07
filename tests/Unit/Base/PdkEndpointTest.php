<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\PdkActions;
use MyParcelNL\Pdk\Base\PdkEndpoint;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Plugin\Action\PdkActionManager;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkActionManager;
use MyParcelNL\Pdk\Tests\Uses\UsesEachMockPdkInstance;
use function DI\autowire;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(
    new UsesEachMockPdkInstance([
        PdkActionManager::class => autowire(MockPdkActionManager::class),
    ])
);

it('calls pdk endpoints', function (string $action) {
    /** @var \MyParcelNL\Pdk\Base\PdkEndpoint $endpoint */
    $endpoint = Pdk::get(PdkEndpoint::class);
    $endpoint->call($action);

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdkActionManager $manager */
    $manager  = Pdk::get(PdkActionManager::class);
    $requests = $manager->getRequests();

    expect($requests->all())
        ->toHaveLength(1)
        ->and($requests->first())
        ->toEqual(['action' => $action]);
})->with([
    'export order'           => ['action' => PdkActions::EXPORT_ORDER],
    'export and print order' => ['action' => PdkActions::EXPORT_AND_PRINT_ORDER],
    'get order data'         => ['action' => PdkActions::GET_ORDER_DATA],
    'update tracking number' => ['action' => PdkActions::UPDATE_TRACKING_NUMBER],
]);
