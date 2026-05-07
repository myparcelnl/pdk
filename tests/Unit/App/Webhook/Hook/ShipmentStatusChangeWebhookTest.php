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
use MyParcelNL\Pdk\App\Webhook\Service\ShipmentWebhookService;
use MyParcelNL\Pdk\Base\Concern\PdkInterface;
use MyParcelNL\Pdk\Base\Contract\CronServiceInterface;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetShipmentsResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockApi;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkOrderRepository;
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

class TrackingWebhookPdkOrderRepository extends MockPdkOrderRepository
{
    /**
     * @var int
     */
    public $apiIdentifierLookups = 0;

    public function getByApiIdentifier(string $uuid): ?PdkOrder
    {
        $this->apiIdentifierLookups++;

        return parent::getByApiIdentifier($uuid);
    }
}

function dispatchWebhook(array $hookBody, bool $enqueueShipmentResponse = true, int $apiStatus = 2): array
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
        MockApi::enqueue(new ExampleGetShipmentsResponse([
            array_replace(ExampleGetShipmentsResponse::DEFAULT_SHIPMENT_DATA, [
                'id'     => getWebhookShipmentId($hookBody) ?? 192031595,
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

function getWebhookShipmentId(array $hookBody): ?int
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

function setWebhookAccountFeatures(array $features): void
{
    /** @var \MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface $accountRepository */
    $accountRepository = Pdk::get(PdkAccountRepositoryInterface::class);
    $account           = $accountRepository->getAccount();

    $account->subscriptionFeatures = new Collection($features);
    $accountRepository->store($account);
}

function useTrackingWebhookOrderRepository(): TrackingWebhookPdkOrderRepository
{
    $repository = new TrackingWebhookPdkOrderRepository(Pdk::get(StorageInterface::class));
    $webhookService = new ShipmentWebhookService(
        Pdk::get(\MyParcelNL\Pdk\Account\Contract\AccountFeaturesServiceInterface::class),
        $repository
    );

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdk $pdk */
    $pdk = Pdk::get(PdkInterface::class);
    $pdk->set(PdkOrderRepositoryInterface::class, $repository);
    $pdk->set(ShipmentWebhookService::class, $webhookService);
    $pdk->set(ShipmentStatusChangeWebhook::class, new ShipmentStatusChangeWebhook($webhookService));

    return $repository;
}

function validHookBody($orderId = 'api-uuid-string', $referenceIdentifier = null): array
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

it('dispatches update action when order_id is valid for order v1', function () {
    setWebhookAccountFeatures([PdkAccountFeaturesService::FEATURE_LEGACY_ORDER_MANAGEMENT]);

    $result = dispatchWebhook(validHookBody('api-uuid-string'));
    $call = $result['actions']->getCalls()->firstWhere('action', PdkBackendActions::UPDATE_SHIPMENTS);

    expect($call['parameters']['orderIds'])
        ->toBe(['197'])
        ->and($call['parameters']['shipmentIds'])
        ->toBe([192031595])
        ->and($call['parameters']['useShipmentStatusForOrderStatus'])
        ->toBeTrue()
        ->and($call['parameters'])
        ->not->toHaveKey('orderStatus');
});

it('dispatches update action when shipment_reference_identifier is valid for shipments mode', function () {
    $body = validHookBody(null, 'REF-123');
    unset($body['shipment_id']);
    $body['shipmentId'] = 192031595;

    $result = dispatchWebhook($body);
    $call = $result['actions']->getCalls()->firstWhere('action', PdkBackendActions::UPDATE_SHIPMENTS);

    expect($call['parameters']['orderIds'])
        ->toBe(['REF-123'])
        ->and($call['parameters']['shipmentIds'])
        ->toBe([192031595]);
});

it('chooses the order identifier by order mode', function (array $features, array $body, array $expectedOrderIds) {
    setWebhookAccountFeatures($features);

    $result = dispatchWebhook($body);
    $call = $result['actions']->getCalls()->firstWhere('action', PdkBackendActions::UPDATE_SHIPMENTS);

    expect($call['parameters']['orderIds'])->toBe($expectedOrderIds);
})->with([
    'shipments only uses shipment_reference_identifier' => [
        [],
        validHookBody('api-uuid-string', 'REF-123'),
        ['REF-123'],
    ],
    'order v1 uses order_id' => [
        [PdkAccountFeaturesService::FEATURE_LEGACY_ORDER_MANAGEMENT],
        validHookBody('api-uuid-string', 'REF-123'),
        ['197'],
    ],
    'order v2 uses shipment_reference_identifier' => [
        [PdkAccountFeaturesService::FEATURE_ORDER_MANAGEMENT],
        validHookBody('api-uuid-string', '197'),
        ['197'],
    ],
]);

it('does not resolve order v2 webhooks by legacy api identifier', function () {
    $repository = useTrackingWebhookOrderRepository();

    setWebhookAccountFeatures([PdkAccountFeaturesService::FEATURE_ORDER_MANAGEMENT]);

    dispatchWebhook(validHookBody('legacy-api-uuid', '197'));

    expect($repository->apiIdentifierLookups)->toBe(0);
});

it('skips order v2 webhook when only the legacy order_id is present', function () {
    setWebhookAccountFeatures([PdkAccountFeaturesService::FEATURE_ORDER_MANAGEMENT]);

    $result = dispatchWebhook(validHookBody('api-uuid-string'), false);

    expect($result['actions']->getCalls()->pluck('action')->contains(PdkBackendActions::UPDATE_SHIPMENTS))
        ->toBeFalse()
        ->and($result['logger']->getLogs('debug'))
        ->toContain([
            'level'   => 'debug',
            'message' => '[PDK]: Skipping shipment status change webhook without a valid order identifier',
            'context' => [
                'shipment_id'                   => 192031595,
                'order_id'                      => 'api-uuid-string',
                'shipment_reference_identifier' => null,
            ],
        ]);
});

it('skips shipment updates when the shipment id is missing', function () {
    setWebhookAccountFeatures([PdkAccountFeaturesService::FEATURE_LEGACY_ORDER_MANAGEMENT]);

    $body = validHookBody('api-uuid-string');
    unset($body['shipment_id']);

    $result = dispatchWebhook($body, false);

    expect($result['actions']->getCalls()->pluck('action')->contains(PdkBackendActions::UPDATE_SHIPMENTS))
        ->toBeFalse()
        ->and($result['logger']->getLogs('debug'))
        ->toContain([
            'level'   => 'debug',
            'message' => '[PDK]: Skipping shipment webhook without a shipment id',
            'context' => [
                'shipment_id'                   => null,
                'order_id'                      => 'api-uuid-string',
                'shipment_reference_identifier' => null,
            ],
        ]);
});

it('uses the fetched shipment status for order status updates instead of the webhook status', function () {
    setWebhookAccountFeatures([PdkAccountFeaturesService::FEATURE_LEGACY_ORDER_MANAGEMENT]);

    $body = validHookBody();
    $body['status'] = 9;

    $result = dispatchWebhook($body, true, 5);

    $statusLog = (new Collection($result['logger']->getLogs()))
        ->filter(fn(array $log) => str_starts_with($log['message'], '[PDK]: Update status'))
        ->first();

    expect($statusLog['context']['status'])->toBe(OrderSettings::STATUS_WHEN_LABEL_SCANNED);
});

it('skips webhook when identifiers are missing or empty', function ($orderId, $referenceIdentifier) {
    $hookBody = validHookBody($orderId, $referenceIdentifier);
    $result   = dispatchWebhook($hookBody, false);

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
