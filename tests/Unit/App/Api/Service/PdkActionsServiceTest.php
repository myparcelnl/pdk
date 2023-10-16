<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Api\Service;

use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Api\Contract\PdkActionsServiceInterface;
use MyParcelNL\Pdk\App\Api\PdkEndpoint;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('executes actions', function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdkActionsService $actions */
    $actions = Pdk::get(PdkActionsServiceInterface::class);

    $actions
        ->setContext(PdkEndpoint::CONTEXT_BACKEND)
        ->execute(PdkBackendActions::FETCH_ORDERS, ['orderIds' => 1]);

    $calls = $actions->getCalls();

    expect($calls)
        ->toHaveLength(1)
        ->and($calls[0])
        ->toBe([
            'action'     => PdkBackendActions::FETCH_ORDERS,
            'parameters' => ['orderIds' => 1],
        ]);
});
