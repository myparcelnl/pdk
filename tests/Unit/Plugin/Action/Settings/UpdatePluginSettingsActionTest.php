<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Unit\Plugin\Action\Settings;

use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Plugin\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\Plugin\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkOrderRepository;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function DI\autowire;
use function MyParcelNL\Pdk\Tests\usesShared;

uses()->group('settings');

usesShared(
    new UsesMockPdkInstance([
        PdkOrderRepositoryInterface::class => autowire(MockPdkOrderRepository::class),
    ])
);

it('saves settings', function (array $data) {
    $content = json_encode([
        'data' => [
            'plugin_settings' => [
                'general' => [
                    'order_mode' => $data['order_mode'],
                ],
            ],
        ],
    ]);
    $request = new Request(['action' => PdkBackendActions::UPDATE_PLUGIN_SETTINGS], [], [], [], [], [], $content);

    $response = Actions::execute($request);

    if (! $response) {
        throw new RuntimeException('Response is empty');
    }

    $content = json_decode($response->getContent(), true);

    expect($response)
        ->toBeInstanceOf(Response::class)
        ->and(Arr::dot($content))
        ->toHaveKeysAndValues([
            'data.plugin_settings.general.orderMode' => $data['order_mode'],
        ])
        ->and($response->getStatusCode())
        ->toBe(200);
})->with([
    [['order_mode' => false]],
    [['order_mode' => true]],
]);
