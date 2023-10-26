<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Order;

use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\App\Order\Contract\OrderStatusServiceInterface;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;
use MyParcelNL\Pdk\Tests\Uses\UsesApiMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Tests\Uses\UsesNotificationsMock;
use MyParcelNL\Pdk\Tests\Uses\UsesSettingsMock;
use Symfony\Component\HttpFoundation\Response;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance(), new UsesApiMock(), new UsesNotificationsMock(), new UsesSettingsMock());

it('updates order status', function (string $settingName, $value, $result) {
    factory(OrderSettings::class)
        ->{"with$settingName"}(
            $value
        )
        ->store();

    $orders = factory(PdkOrderCollection::class, 1)
        ->store();

    $orderIds = Arr::pluck($orders, 'externalIdentifier');

    $response = Actions::execute(PdkBackendActions::UPDATE_ORDER_STATUS, [
        'orderIds' => $orderIds,
        'setting'  => $settingName,
    ]);

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockOrderStatusService $orderStatusService */
    $orderStatusService = Pdk::get(OrderStatusServiceInterface::class);

    $updates = $orderStatusService->getUpdates();

    expect($response)
        ->toBeInstanceOf(Response::class)
        ->and($response->getStatusCode())
        ->toBe(204);

    if ($result === null) {
        expect($updates)->toBeEmpty();
    } else {
        expect($updates)->toBe([
            [
                'orderIds' => $orderIds,
                'status'   => $result,
            ],
        ]);
    }
})
    ->with([
        'status on label create'  => [
            OrderSettings::STATUS_ON_LABEL_CREATE,
        ],
        'status on label print'   => [
            OrderSettings::STATUS_WHEN_DELIVERED,
        ],
        'status on label scanned' => [
            OrderSettings::STATUS_WHEN_LABEL_SCANNED,
        ],
    ])
    ->with([
        'non-empty status'      => [
            'status' => 'test',
            'result' => 'test',
        ],
        'empty status, no call' => [
            'status' => -1,
            'result' => null,
        ],
        'null status, no call'  => [
            'status' => null,
            'result' => null,
        ],
    ]);

it('does nothing when no setting is passed', function () {
    $response = Actions::execute(PdkBackendActions::UPDATE_ORDER_STATUS, [
        'orderIds' => '1234',
    ]);

    expect($response->getStatusCode())->toBe(204);
});
