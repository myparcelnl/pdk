<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Settings;

use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkOrderRepository;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function DI\autowire;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(
    new UsesMockPdkInstance([
        PdkOrderRepositoryInterface::class => autowire(MockPdkOrderRepository::class),
    ])
);

it('saves settings', function (array $data) {
    $content = json_encode([
        'data' => [
            'plugin_settings' => [
                'order' => [
                    'order_mode' => $data['order_mode'],
                ],
            ],
        ],
    ]);
    $request = new Request(['action' => PdkBackendActions::UPDATE_PLUGIN_SETTINGS], [], [], [], [], [], $content);

    $response = Actions::execute($request);

    $content = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

    expect($response)
        ->toBeInstanceOf(Response::class)
        ->and(Arr::dot($content))
        ->toHaveKeysAndValues([
            'data.plugin_settings.order.orderMode' => $data['order_mode'],
        ])
        ->and($response->getStatusCode())
        ->toBe(200);
})->with([
    [['order_mode' => false]],
    [['order_mode' => true]],
]);
