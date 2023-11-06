<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Api\Service;

use InvalidArgumentException;
use MyParcelNL\Pdk\Api\Exception\PdkEndpointException;
use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Api\Contract\PdkActionsServiceInterface;
use MyParcelNL\Pdk\App\Api\PdkEndpoint;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use Symfony\Component\HttpFoundation\Request;
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

it('can label an action as automatic', function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdkActionsService $actions */
    $actions = Pdk::get(PdkActionsServiceInterface::class);

    $actions
        ->setContext(PdkEndpoint::CONTEXT_BACKEND)
        ->executeAutomatic(PdkBackendActions::FETCH_ORDERS, ['orderIds' => 1]);

    $calls = $actions->getCalls();

    expect($calls)
        ->toHaveLength(1)
        ->and($calls[0])
        ->toBe([
            'action'     => PdkBackendActions::FETCH_ORDERS,
            'parameters' => [
                'orderIds'   => 1,
                'actionType' => 'automatic',
            ],
        ]);
});

it('throws error if action parameter is missing', function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdkActionsService $actions */
    $actions = Pdk::get(PdkActionsServiceInterface::class);

    expect(function () use ($actions) {
        $actions->execute(new Request());
    })->toThrow(InvalidArgumentException::class);
});

it('throws error if input is not a string or Request', function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdkActionsService $actions */
    $actions = Pdk::get(PdkActionsServiceInterface::class);

    expect(function () use ($actions) {
        /* @phpstan-ignore-next-line */
        $actions->execute(1);
    })->toThrow(InvalidArgumentException::class);
});

it('throws error if context is invalid', function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdkActionsService $actions */
    $actions = Pdk::get(PdkActionsServiceInterface::class);

    expect(function () use ($actions) {
        $actions
            ->setContext('invalid')
            ->execute(PdkBackendActions::FETCH_ORDERS);
    })->toThrow(PdkEndpointException::class);
});
