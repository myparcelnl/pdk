<?php

/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Webhook\Hook;

use MyParcelNL\Pdk\Account\Service\PdkAccountFeaturesService;
use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Api\Contract\PdkActionsServiceInterface;
use MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhookManagerInterface;
use MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhooksRepositoryInterface;
use MyParcelNL\Pdk\Base\Contract\CronServiceInterface;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;
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

function dispatchShipmentLabelCreatedWebhook(
    array $hookBody,
    bool $enqueueShipmentResponse = true,
    int $apiStatus = 2
): array {
    /** @var \MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhooksRepositoryInterface $repository */
    $repository = Pdk::get(PdkWebhooksRepositoryInterface::class);
    /** @var \MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhookManagerInterface $webhookManager */
    $webhookManager = Pdk::get(PdkWebhookManagerInterface::class);
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockCronService $cronService */
    $cronService = Pdk::get(CronServiceInterface::class);
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockLogger $logger */
    $logger = Pdk::get(LoggerInterface::class);
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdkActionsService $actions */
    $actions = Pdk::get(PdkActionsServiceInterface::class);

    $repository->storeHashedUrl('https://example.com/hook/1234567890abcdef');
    $repository->store(new WebhookSubscriptionCollection([[
        'hook' => WebhookSubscription::SHIPMENT_LABEL_CREATED,
        'url'  => $repository->getHashedUrl(),
    ]]));

    if ($enqueueShipmentResponse) {
        MockApi::enqueue(new ExampleGetShipmentsResponse([
            array_replace(ExampleGetShipmentsResponse::DEFAULT_SHIPMENT_DATA, [
                'id'     => getLabelCreatedWebhookShipmentId($hookBody) ?? 231032886,
                'status' => $apiStatus,
            ]),
        ]));
    }

    $request = Request::create(
        $repository->getHashedUrl(),
        Request::METHOD_POST,
        [],
        [],
        [],
        ['HTTP_X_MYPARCEL_HOOK' => WebhookSubscription::SHIPMENT_LABEL_CREATED],
        json_encode([
            'data' => [
                'hooks' => [
                    array_merge(['event' => WebhookSubscription::SHIPMENT_LABEL_CREATED], $hookBody),
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

function getLabelCreatedWebhookShipmentId(array $hookBody): ?int
{
    $content = isset($hookBody['shipment']) && is_array($hookBody['shipment'])
        ? array_replace($hookBody, $hookBody['shipment'])
        : $hookBody;

    foreach (['shipment_id', 'shipmentId', 'id'] as $key) {
        if (isset($content[$key]) && is_numeric($content[$key])) {
            return (int) $content[$key];
        }
    }

    return null;
}

function setShipmentLabelCreatedAccountFeatures(array $features): void
{
    /** @var \MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface $accountRepository */
    $accountRepository = Pdk::get(PdkAccountRepositoryInterface::class);
    $account           = $accountRepository->getAccount();

    $account->subscriptionFeatures = new Collection($features);
    $accountRepository->store($account);
}

function validShipmentLabelCreatedHookBody(array $overrides = []): array
{
    return array_replace([
        'shipment_reference_identifier' => '197',
        'shipment_id'                   => 231032886,
        'status'                        => 2,
        'barcode'                       => '3SMYPA428613388',
    ], $overrides);
}

it('dispatches update shipments for order v2 label created webhooks', function () {
    setShipmentLabelCreatedAccountFeatures([PdkAccountFeaturesService::FEATURE_ORDER_MANAGEMENT]);

    $result = dispatchShipmentLabelCreatedWebhook(validShipmentLabelCreatedHookBody());
    $call   = $result['actions']->getCalls()->firstWhere('action', PdkBackendActions::UPDATE_SHIPMENTS);

    expect($call['parameters'])
        ->toMatchArray([
            'orderIds'                        => ['197'],
            'shipmentIds'                     => [231032886],
            'useShipmentStatusForOrderStatus' => true,
            'linkFirstShipmentToFirstOrder'   => true,
        ])
        ->and($call['parameters'])
        ->not->toHaveKey('orderStatus');
});

it('reads shipment id aliases for order v2 label created webhooks', function (array $body, int $expectedShipmentId) {
    setShipmentLabelCreatedAccountFeatures([PdkAccountFeaturesService::FEATURE_ORDER_MANAGEMENT]);

    $result = dispatchShipmentLabelCreatedWebhook($body);
    $call   = $result['actions']->getCalls()->firstWhere('action', PdkBackendActions::UPDATE_SHIPMENTS);

    expect($call['parameters']['shipmentIds'])->toBe([$expectedShipmentId]);
})->with([
    'shipment_id' => [
        validShipmentLabelCreatedHookBody(['shipment_id' => 231032886]),
        231032886,
    ],
    'shipmentId'  => [
        validShipmentLabelCreatedHookBody([
            'shipment_id' => null,
            'shipmentId'  => 231032887,
        ]),
        231032887,
    ],
    'id'          => [
        validShipmentLabelCreatedHookBody([
            'shipment_id' => null,
            'id'          => 231032888,
        ]),
        231032888,
    ],
    'nested'      => [
        validShipmentLabelCreatedHookBody([
            'shipment_id' => null,
            'shipment'    => [
                'shipment_id' => 231032889,
            ],
        ]),
        231032889,
    ],
]);

it('uses fetched shipment status for order status updates instead of webhook status', function () {
    setShipmentLabelCreatedAccountFeatures([PdkAccountFeaturesService::FEATURE_ORDER_MANAGEMENT]);

    $body = validShipmentLabelCreatedHookBody(['status' => 9]);

    $result = dispatchShipmentLabelCreatedWebhook($body, true, 5);

    $statusLog = (new Collection($result['logger']->getLogs()))
        ->filter(fn(array $log) => str_starts_with($log['message'], '[PDK]: Update status'))
        ->first();

    expect($statusLog['context']['status'])->toBe(OrderSettings::STATUS_WHEN_LABEL_SCANNED);
});

it('skips label created webhook when shipment reference identifier is missing', function () {
    setShipmentLabelCreatedAccountFeatures([PdkAccountFeaturesService::FEATURE_ORDER_MANAGEMENT]);

    $body = validShipmentLabelCreatedHookBody(['shipment_reference_identifier' => null]);

    $result = dispatchShipmentLabelCreatedWebhook($body, false);

    expect($result['actions']->getCalls()->pluck('action')->contains(PdkBackendActions::UPDATE_SHIPMENTS))
        ->toBeFalse()
        ->and($result['logger']->getLogs('debug'))
        ->toContain([
            'level'   => 'debug',
            'message' => '[PDK]: Skipping shipment label created webhook without a shipment reference identifier',
            'context' => [
                'shipment_id'                   => 231032886,
                'order_id'                      => null,
                'shipment_reference_identifier' => null,
            ],
        ]);
});

it('skips order v2 label created webhook when shipment id is missing', function () {
    setShipmentLabelCreatedAccountFeatures([PdkAccountFeaturesService::FEATURE_ORDER_MANAGEMENT]);

    $body = validShipmentLabelCreatedHookBody(['shipment_id' => null]);

    $result = dispatchShipmentLabelCreatedWebhook($body, false);

    expect($result['actions']->getCalls()->pluck('action')->contains(PdkBackendActions::UPDATE_SHIPMENTS))
        ->toBeFalse()
        ->and($result['logger']->getLogs('debug'))
        ->toContain([
            'level'   => 'debug',
            'message' => '[PDK]: Skipping shipment webhook without a shipment id',
            'context' => [
                'shipment_id'                   => null,
                'order_id'                      => null,
                'shipment_reference_identifier' => '197',
            ],
        ]);
});

it('keeps shipment label created as a no-op for order v1', function () {
    setShipmentLabelCreatedAccountFeatures([PdkAccountFeaturesService::FEATURE_LEGACY_ORDER_MANAGEMENT]);

    $result = dispatchShipmentLabelCreatedWebhook(validShipmentLabelCreatedHookBody(), false);

    expect($result['actions']->getCalls())->toHaveCount(0);
});

it('dispatches update shipments for label created webhooks in shipments-only mode', function () {
    setShipmentLabelCreatedAccountFeatures([]);

    $result = dispatchShipmentLabelCreatedWebhook(validShipmentLabelCreatedHookBody());
    $call   = $result['actions']->getCalls()->firstWhere('action', PdkBackendActions::UPDATE_SHIPMENTS);

    expect($call['parameters'])
        ->toMatchArray([
            'orderIds'                        => ['197'],
            'shipmentIds'                     => [231032886],
            'useShipmentStatusForOrderStatus' => true,
            'linkFirstShipmentToFirstOrder'   => true,
        ]);
});
