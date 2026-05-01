<?php

/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Webhook\Hook;

use MyParcelNL\Pdk\Account\Service\PdkAccountFeaturesService;
use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Api\Contract\PdkActionsServiceInterface;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhookManagerInterface;
use MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhooksRepositoryInterface;
use MyParcelNL\Pdk\Base\Contract\CronServiceInterface;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetShipmentsResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockApi;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockEachCron;
use MyParcelNL\Pdk\Tests\Uses\UsesMockEachLogger;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Webhook\Collection\WebhookSubscriptionCollection;
use MyParcelNL\Pdk\Webhook\Model\WebhookSubscription;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use function MyParcelNL\Pdk\Tests\usesShared;

uses()->group('webhook');

usesShared(new UsesMockPdkInstance(), new UsesMockEachCron(), new UsesMockEachLogger(), new UsesAccountMock());

function dispatchWebhook(array $hookBody, bool $enqueueShipmentResponse = true): array
{
    /** @var PdkWebhooksRepositoryInterface $repository */
    $repository = Pdk::get(PdkWebhooksRepositoryInterface::class);
    /** @var PdkWebhookManagerInterface $webhookManager */
    $webhookManager = Pdk::get(PdkWebhookManagerInterface::class);
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockCronService $cronService */
    $cronService = Pdk::get(CronServiceInterface::class);
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockLogger $logger */
    $logger = Pdk::get(LoggerInterface::class);
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdkActionsService $actions */
    $actions = Pdk::get(PdkActionsServiceInterface::class);

    $repository->storeHashedUrl('https://example.com/hook/1234567890abcdef');
    $repository->store(new WebhookSubscriptionCollection([[
        'hook' => WebhookSubscription::SHIPMENT_STATUS_CHANGE,
        'url'  => $repository->getHashedUrl(),
    ]]));
    if ($enqueueShipmentResponse) {
        MockApi::enqueue(new ExampleGetShipmentsResponse());
    }

    $request = Request::create(
        $repository->getHashedUrl(),
        Request::METHOD_POST,
        [],
        [],
        [],
        ['HTTP_X_MYPARCEL_HOOK' => WebhookSubscription::SHIPMENT_STATUS_CHANGE],
        json_encode([
            'data' => [
                'hooks' => [
                    array_merge(['event' => WebhookSubscription::SHIPMENT_STATUS_CHANGE], $hookBody),
                ],
            ],
        ])
    );

    $webhookManager->call($request);
    $cronService->executeScheduledTask();

    return [
        'actions' => $actions,
        'logger'  => $logger,
    ];
}

function setWebhookAccountFeatures(array $features): void
{
    /** @var \MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface $accountRepository */
    $accountRepository = Pdk::get(PdkAccountRepositoryInterface::class);
    $account           = $accountRepository->getAccount();

    $account->subscriptionFeatures = new Collection($features);
    $accountRepository->store($account);
}

function storeOrderWithShipment(string $orderId = '197', int $shipmentId = 192031595): PdkOrder
{
    /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface $orderRepository */
    $orderRepository = Pdk::get(PdkOrderRepositoryInterface::class);

    $order = new PdkOrder([
        'externalIdentifier' => $orderId,
        'shipments'          => [
            [
                'id'      => $shipmentId,
                'orderId' => $orderId,
                'status'  => 1,
                'barcode' => 'old-barcode',
            ],
        ],
    ]);

    return $orderRepository->update($order);
}


function validHookBody($orderId = 'api-uuid-string', $referenceIdentifier = null)
{
    $body = [
        'shipment_id' => 192031595,
        'account_id'  => 162450,
        'shop_id'     => 83287,
        'status'      => 2,
        'barcode'     => '3SHOHR763563926',
    ];

    if ($orderId !== null) {
        $body['order_id'] = $orderId;
    }

    if ($referenceIdentifier !== null) {
        $body['shipment_reference_identifier'] = $referenceIdentifier;
    }

    return $body;
}


it('dispatches update action when order_id is valid', function () {
    setWebhookAccountFeatures([PdkAccountFeaturesService::FEATURE_LEGACY_ORDER_MANAGEMENT]);

    $result = dispatchWebhook(validHookBody('api-uuid-string'));

    expect($result['actions']->getCalls()->pluck('action')->contains(PdkBackendActions::UPDATE_SHIPMENTS))
        ->toBeTrue();
});


it('dispatches update action when shipment_reference_identifier is valid', function () {
    $result = dispatchWebhook(validHookBody(null, 'REF-123'));

    expect($result['actions']->getCalls()->pluck('action')->contains(PdkBackendActions::UPDATE_SHIPMENTS))
        ->toBeTrue()
        ->and($result['actions']
            ->getCalls()
            ->firstWhere('action', PdkBackendActions::UPDATE_SHIPMENTS)['parameters']['orderIds'])
        ->toBe(['REF-123']);
});

it('updates existing shipments locally in order v2 without fetching shipment data from the api', function () {
    setWebhookAccountFeatures([PdkAccountFeaturesService::FEATURE_ORDER_MANAGEMENT]);
    storeOrderWithShipment('197');

    $body           = validHookBody('legacy-api-uuid', '197');
    $body['status'] = 9;
    $result         = dispatchWebhook($body, false);
    $calls          = $result['actions']->getCalls();

    /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface $orderRepository */
    $orderRepository = Pdk::get(PdkOrderRepositoryInterface::class);
    $order           = $orderRepository->find('197');
    $shipment        = $order->shipments->first(function (Shipment $shipment) {
        return 192031595 === (int) $shipment->id;
    });

    expect($calls->pluck('action')->contains(PdkBackendActions::UPDATE_SHIPMENTS))
        ->toBeFalse()
        ->and($calls->pluck('action')->contains(PdkBackendActions::UPDATE_ORDER_STATUS))
        ->toBeTrue()
        ->and($calls->firstWhere('action', PdkBackendActions::UPDATE_ORDER_STATUS)['parameters'])
        ->toBe([
            'orderIds' => ['197'],
            'setting'  => OrderSettings::getStatus(9),
        ])
        ->and($shipment->status)
        ->toBe(9)
        ->and($shipment->barcode)
        ->toBe('3SHOHR763563926');
});

it('skips order v2 webhook when only the legacy order_id is present', function () {
    setWebhookAccountFeatures([PdkAccountFeaturesService::FEATURE_ORDER_MANAGEMENT]);
    storeOrderWithShipment('197');

    $message = '[PDK]: Skipping order v2 shipment status change webhook without a shipment reference identifier';
    $result = dispatchWebhook(validHookBody('api-uuid-string'), false);

    expect($result['actions']->getCalls()->pluck('action')->contains(PdkBackendActions::UPDATE_SHIPMENTS))
        ->toBeFalse()
        ->and($result['actions']->getCalls()->pluck('action')->contains(PdkBackendActions::UPDATE_ORDER_STATUS))
        ->toBeFalse()
        ->and($result['logger']->getLogs('debug'))
        ->toContain([
            'level'   => 'debug',
            'message' => $message,
            'context' => [
                'shipment_id'                   => 192031595,
                'order_id'                      => 'api-uuid-string',
                'shipment_reference_identifier' => null,
            ],
        ]);
});


it('skips webhook when identifiers are missing or empty', function ($orderId, $referenceIdentifier) {
    $hookBody = validHookBody($orderId, $referenceIdentifier);
    $result   = dispatchWebhook($hookBody);

    expect($result['actions']->getCalls()->pluck('action')->contains(PdkBackendActions::UPDATE_SHIPMENTS))
        ->toBeFalse()
        ->and($result['logger']->getLogs('debug'))
        ->toContain([
            'level'   => 'debug',
            'message' => '[PDK]: Skipping shipment status change webhook without a valid order identifier',
            'context' => [
                'shipment_id'                   => $hookBody['shipment_id'],
                'order_id'                      => $hookBody['order_id'] ?? null,
                'shipment_reference_identifier' => $hookBody['shipment_reference_identifier'] ?? null,
            ],
        ]);
})->with([
    'order_id empty, reference empty'              => ['', ''],
    'order_id empty, reference whitespace'         => ['', '   '],
    'order_id whitespace, reference not present'   => ['   ', null],
    'order_id not present, reference empty'        => [null, ''],
    'both not present'                             => [null, null],
    'both whitespace'                              => ['  ', '  '],
]);

it('maps shipment status to correct order status', function (int $status, string $expectedStatus) {
    setWebhookAccountFeatures([PdkAccountFeaturesService::FEATURE_LEGACY_ORDER_MANAGEMENT]);

    $body = validHookBody();
    $body['status'] = $status;
    $result = dispatchWebhook($body);

    expect($result['actions']->getCalls()->pluck('action')->contains(PdkBackendActions::UPDATE_SHIPMENTS))
        ->toBeTrue();

    $statusLog = (new Collection($result['logger']->getLogs()))
        ->filter(fn(array $log) => str_starts_with($log['message'], '[PDK]: Update status'))
        ->first();

    expect($statusLog['context']['status'])->toBe($expectedStatus);
})->with([
    'status 2 - label created'  => [2, OrderSettings::STATUS_ON_LABEL_CREATE],
    'status 5 - label scanned'  => [5, OrderSettings::STATUS_WHEN_LABEL_SCANNED],
    'status 9 - delivered'      => [9, OrderSettings::STATUS_WHEN_DELIVERED],
]);

it('logs webhook received and processed', function () {
    setWebhookAccountFeatures([PdkAccountFeaturesService::FEATURE_LEGACY_ORDER_MANAGEMENT]);

    $result = dispatchWebhook(validHookBody());

    $logs = (new Collection($result['logger']->getLogs()))
        ->map(function (array $log) {
            unset($log['context']['request']);
            return $log;
        })
        ->filter(fn(array $log) => in_array($log['message'], ['[PDK]: Webhook received', '[PDK]: Webhook processed']))
        ->values();

    expect($logs->toArray())->toBe([
        [
            'level'   => 'debug',
            'message' => '[PDK]: Webhook received',
            'context' => [],
        ],
        [
            'level'   => 'debug',
            'message' => '[PDK]: Webhook processed',
            'context' => ['hook' => ShipmentStatusChangeWebhook::class],
        ],
    ]);
});
