<?php

/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Webhook\Hook;

use MyParcelNL\Pdk\Account\Contract\AccountFeaturesServiceInterface;
use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Api\Contract\PdkActionsServiceInterface;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderNoteRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Shipment\Service\ShipmentUpdateService;
use MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhookManagerInterface;
use MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhooksRepositoryInterface;
use MyParcelNL\Pdk\App\Webhook\Service\ShipmentWebhookService;
use MyParcelNL\Pdk\Base\Concern\PdkInterface;
use MyParcelNL\Pdk\Base\Contract\CronServiceInterface;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkOrderRepository;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockEachCron;
use MyParcelNL\Pdk\Tests\Uses\UsesMockEachLogger;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Webhook\Collection\WebhookSubscriptionCollection;
use MyParcelNL\Pdk\Webhook\Model\WebhookSubscription;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

uses()->group('webhook');

usesShared(new UsesMockPdkInstance(), new UsesMockEachCron(), new UsesMockEachLogger(), new UsesAccountMock());

class StrictShipmentLabelCreatedOrderRepository extends MockPdkOrderRepository
{
    /**
     * @var \MyParcelNL\Pdk\App\Order\Model\PdkOrder[]
     */
    private $orders = [];

    public function find($id): ?PdkOrder
    {
        return $this->orders[(string) $id] ?? null;
    }

    public function update(PdkOrder $order): PdkOrder
    {
        $order = parent::update($order);

        $this->orders[(string) $order->externalIdentifier] = $order;

        return $order;
    }
}

function dispatchShipmentLabelCreatedWebhook(array $hookBody): array
{
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

function useStrictShipmentLabelCreatedOrderRepository(): void
{
    $repository = new StrictShipmentLabelCreatedOrderRepository(Pdk::get(StorageInterface::class));
    $updateService = new ShipmentUpdateService($repository, Pdk::get(PdkOrderNoteRepositoryInterface::class));
    $webhookService = new ShipmentWebhookService(
        Pdk::get(AccountFeaturesServiceInterface::class),
        $repository,
        $updateService
    );

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdk $pdk */
    $pdk = Pdk::get(PdkInterface::class);
    $pdk->set(PdkOrderRepositoryInterface::class, $repository);
    $pdk->set(ShipmentUpdateService::class, $updateService);
    $pdk->set(ShipmentWebhookService::class, $webhookService);
    $pdk->set(ShipmentLabelCreatedWebhook::class, new ShipmentLabelCreatedWebhook($webhookService));
}

function setShipmentLabelCreatedAccountFeatures(array $features): void
{
    /** @var \MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface $accountRepository */
    $accountRepository = Pdk::get(PdkAccountRepositoryInterface::class);
    $account           = $accountRepository->getAccount();

    $account->subscriptionFeatures = new Collection($features);
    $accountRepository->store($account);
}

function storeOrderForShipmentLabelCreated(string $orderId = '197', array $shipments = []): PdkOrder
{
    /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface $orderRepository */
    $orderRepository = Pdk::get(PdkOrderRepositoryInterface::class);

    return $orderRepository->update(new PdkOrder([
        'externalIdentifier' => $orderId,
        'shipments'          => $shipments,
    ]));
}

function getShipmentFromLabelCreatedOrder(string $orderId, int $shipmentId): ?Shipment
{
    /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface $orderRepository */
    $orderRepository = Pdk::get(PdkOrderRepositoryInterface::class);
    $order           = $orderRepository->find($orderId);

    return $order->shipments->first(function (Shipment $shipment) use ($shipmentId) {
        return $shipmentId === (int) $shipment->id;
    });
}

it('stores order v2 label created shipment data locally', function () {
    setShipmentLabelCreatedAccountFeatures([AccountFeaturesServiceInterface::FEATURE_ORDER_MANAGEMENT]);
    storeOrderForShipmentLabelCreated('197');

    $result = dispatchShipmentLabelCreatedWebhook([
        'shipment_reference_identifier' => '197',
        'shipment'                      => [
            'shipment_id'         => 231032886,
            'status'              => 2,
            'barcode'             => '3SMYPA428613388',
            'external_identifier' => 'external-shipment-id',
        ],
    ]);

    $shipment = getShipmentFromLabelCreatedOrder('197', 231032886);

    expect($shipment)
        ->toBeInstanceOf(Shipment::class)
        ->and($shipment->orderId)
        ->toBe('197')
        ->and($shipment->barcode)
        ->toBe('3SMYPA428613388')
        ->and($shipment->externalIdentifier)
        ->toBe('external-shipment-id')
        ->and($result['actions']->getCalls()->pluck('action')->contains(PdkBackendActions::UPDATE_SHIPMENTS))
        ->toBeFalse()
        ->and($result['actions']
            ->getCalls()
            ->firstWhere('action', PdkBackendActions::UPDATE_ORDER_STATUS)['parameters'])
        ->toBe([
            'orderIds' => ['197'],
            'setting'  => OrderSettings::STATUS_ON_LABEL_CREATE,
        ]);
});

it('merges order v2 label created shipment data without regressing newer status data', function () {
    setShipmentLabelCreatedAccountFeatures([AccountFeaturesServiceInterface::FEATURE_ORDER_MANAGEMENT]);
    storeOrderForShipmentLabelCreated('197', [[
        'id'                 => 231032886,
        'orderId'            => '197',
        'barcode'            => 'old-barcode',
        'status'             => 9,
        'linkConsumerPortal' => 'https://old.example',
    ]]);

    $result = dispatchShipmentLabelCreatedWebhook([
        'shipment_id'                   => 231032886,
        'status'                        => 2,
        'barcode'                       => '3SMYPA428613388',
        'shipment_reference_identifier' => '197',
    ]);

    $shipment = getShipmentFromLabelCreatedOrder('197', 231032886);

    expect($shipment->status)
        ->toBe(9)
        ->and($shipment->barcode)
        ->toBe('3SMYPA428613388')
        ->and($shipment->linkConsumerPortal)
        ->toBe('https://old.example')
        ->and($result['actions']->getCalls()->pluck('action')->contains(PdkBackendActions::UPDATE_SHIPMENTS))
        ->toBeFalse();
});

it('adds barcode note for order v2 label created webhook', function () {
    setShipmentLabelCreatedAccountFeatures([AccountFeaturesServiceInterface::FEATURE_ORDER_MANAGEMENT]);

    factory(OrderSettings::class)
        ->withBarcodeInNote(true)
        ->withBarcodeInNoteTitle('Track:')
        ->store();

    $order = storeOrderForShipmentLabelCreated('197');

    dispatchShipmentLabelCreatedWebhook([
        'shipment_reference_identifier' => '197',
        'shipment'                      => [
            'shipment_id'         => 231032886,
            'status'              => 2,
            'barcode'             => '3SMYPA428613388',
            'external_identifier' => 'external-shipment-id',
        ],
    ]);

    /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderNoteRepositoryInterface $notesRepository */
    $notesRepository = Pdk::get(PdkOrderNoteRepositoryInterface::class);
    $notes           = $notesRepository->getFromOrder($order);

    expect($notes->count())
        ->toBe(1)
        ->and($notes->first()->note)
        ->toBe('Track: 3SMYPA428613388');
});

it('skips order v2 label created webhook for an unknown order', function () {
    useStrictShipmentLabelCreatedOrderRepository();

    setShipmentLabelCreatedAccountFeatures([AccountFeaturesServiceInterface::FEATURE_ORDER_MANAGEMENT]);

    $result = dispatchShipmentLabelCreatedWebhook([
        'shipment_id'                   => 231032886,
        'status'                        => 2,
        'barcode'                       => '3SMYPA428613388',
        'shipment_reference_identifier' => '404',
    ]);

    expect($result['actions']->getCalls()->pluck('action')->contains(PdkBackendActions::UPDATE_ORDER_STATUS))
        ->toBeFalse()
        ->and($result['logger']->getLogs('debug'))
        ->toContain([
            'level'   => 'debug',
            'message' => '[PDK]: Skipping order v2 shipment webhook for unknown order',
            'context' => [
                'shipment_id'                   => 231032886,
                'order_id'                      => null,
                'shipment_reference_identifier' => '404',
            ],
        ]);
});

it('keeps shipment label created as a no-op outside order v2', function (array $features) {
    setShipmentLabelCreatedAccountFeatures($features);
    storeOrderForShipmentLabelCreated('197');

    $result = dispatchShipmentLabelCreatedWebhook([
        'shipment_id'                   => 231032886,
        'status'                        => 2,
        'barcode'                       => '3SMYPA428613388',
        'shipment_reference_identifier' => '197',
    ]);

    /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface $orderRepository */
    $orderRepository = Pdk::get(PdkOrderRepositoryInterface::class);
    $order           = $orderRepository->find('197');

    expect($order->shipments->isEmpty())
        ->toBeTrue()
        ->and($result['actions']->getCalls())
        ->toHaveCount(0);
})->with([
    'shipments only' => [[]],
    'order v1'       => [[AccountFeaturesServiceInterface::FEATURE_LEGACY_ORDER_MANAGEMENT]],
]);
