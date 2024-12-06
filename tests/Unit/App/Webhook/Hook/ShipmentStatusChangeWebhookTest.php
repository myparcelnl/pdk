<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Webhook\Hook;

use MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhookManagerInterface;
use MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhooksRepositoryInterface;
use MyParcelNL\Pdk\Base\Contract\CronServiceInterface;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetShipmentsResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockApi;
use MyParcelNL\Pdk\Tests\Uses\UsesMockEachCron;
use MyParcelNL\Pdk\Tests\Uses\UsesMockEachLogger;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Webhook\Collection\WebhookSubscriptionCollection;
use MyParcelNL\Pdk\Webhook\Model\WebhookSubscription;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use function MyParcelNL\Pdk\Tests\usesShared;

uses()->group('webhook');

usesShared(new UsesMockPdkInstance(), new UsesMockEachCron(), new UsesMockEachLogger());

it('handles an api request', function (string $hook, string $expectedClass, array $hookBody) {
    /** @var PdkWebhooksRepositoryInterface $repository */
    $repository = Pdk::get(PdkWebhooksRepositoryInterface::class);
    /** @var PdkWebhookManagerInterface $webhookManager */
    $webhookManager = Pdk::get(PdkWebhookManagerInterface::class);
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockCronService $cronService */
    $cronService = Pdk::get(CronServiceInterface::class);
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockLogger $logger */
    $logger = Pdk::get(LoggerInterface::class);

    $repository->storeHashedUrl('https://example.com/hook/1234567890abcdef');
    $repository->store(new WebhookSubscriptionCollection([['hook' => $hook, 'url' => $repository->getHashedUrl()]]));
    MockApi::enqueue(new ExampleGetShipmentsResponse());

    $request = Request::create(
        $repository->getHashedUrl(),
        Request::METHOD_POST,
        [],
        [],
        [],
        ['HTTP_X_MYPARCEL_HOOK' => $hook],
        json_encode([
            'data' => [
                'hooks' => [
                    array_merge(['event' => $hook], $hookBody),
                ],
            ],
        ])
    );

    $webhookManager->call($request);
    $cronService->executeScheduledTask();

    $logs = (new Collection($logger->getLogs()))->map(function (array $log) {
        // Omit the request from the logs.
        unset($log['context']['request']);
        return $log;
    });
    // Omit the shipment response from the logs.
    unset($logs[1]);

    expect(array_values($logs->toArray()))->toBe([
        [
            'level'   => 'debug',
            'message' => '[PDK]: Webhook received',
            'context' => [],
        ],
        [
            'level'   => 'debug',
            'message' => '[PDK]: Webhook processed',
            'context' => ['hook' => $expectedClass],
        ],
    ]);
})->with([
    'shipment updated' => [
        'hook'  => WebhookSubscription::SHIPMENT_STATUS_CHANGE,
        'class' => ShipmentStatusChangeWebhook::class,
        'body'  => [
            'shipment_id' => 192031595,
            'account_id' => 162450,
            'order_id' => 'api-uuid-string',
            'shop_id' => 83287,
            'status' => 2,
            'barcode' => '3SHOHR763563926',
            'shipment_reference_identifier' => '',
        ],
    ],
]);
