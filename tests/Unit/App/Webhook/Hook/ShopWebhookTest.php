<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Webhook\Hook;

use MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhookManagerInterface;
use MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhooksRepositoryInterface;
use MyParcelNL\Pdk\Base\Contract\CronServiceInterface;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetOrdersResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockApi;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\Pdk\Tests\Uses\UsesMockEachCron;
use MyParcelNL\Pdk\Tests\Uses\UsesMockEachLogger;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Webhook\Collection\WebhookSubscriptionCollection;
use MyParcelNL\Pdk\Webhook\Model\WebhookSubscription;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance(), new UsesMockEachCron(), new UsesMockEachLogger());

it('executes "update account" action', function (string $hook, string $expectedClass, array $hookBody) {
    /** @var PdkWebhooksRepositoryInterface $repository */
    $repository = Pdk::get(PdkWebhooksRepositoryInterface::class);
    /** @var PdkWebhookManagerInterface $webhookManager */
    $webhookManager = Pdk::get(PdkWebhookManagerInterface::class);
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockCronService $cronService */
    $cronService = Pdk::get(CronServiceInterface::class);
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockLogger $logger */
    $logger = Pdk::get(LoggerInterface::class);

    $repository->store(new WebhookSubscriptionCollection([['hook' => $hook, 'url' => $repository->getHashedUrl()]]));

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

    expect($logs->toArray())->toBe([
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
    'shop updated' => [
        'hook'  => WebhookSubscription::SHOP_UPDATED,
        'class' => ShopUpdatedWebhook::class,
        'body'  => [
            'id' => 1,
        ],
    ],

    'shop carrier configuration updated' => [
        'hook'  => WebhookSubscription::SHOP_CARRIER_CONFIGURATION_UPDATED,
        'class' => ShopCarrierConfigurationUpdatedWebhook::class,
        'body'  => [
            'id' => 1,
        ],
    ],

    'shop carrier accessibility updated' => [
        'hook'  => WebhookSubscription::SHOP_CARRIER_ACCESSIBILITY_UPDATED,
        'class' => ShopCarrierAccessibilityUpdatedWebhook::class,
        'body'  => [
            'id' => 1,
        ],
    ],
]);

it('executes update subscription features action', function (string $hook, string $expectedClass, array $hookBody) {
    /** @var PdkWebhooksRepositoryInterface $repository */
    $repository = Pdk::get(PdkWebhooksRepositoryInterface::class);
    /** @var PdkWebhookManagerInterface $webhookManager */
    $webhookManager = Pdk::get(PdkWebhookManagerInterface::class);
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockCronService $cronService */
    $cronService = Pdk::get(CronServiceInterface::class);
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockLogger $logger */
    $logger = Pdk::get(LoggerInterface::class);

    $repository->store(new WebhookSubscriptionCollection([['hook' => $hook, 'url' => $repository->getHashedUrl()]]));

    TestBootstrapper::hasAccount();

    MockApi::enqueue(new ExampleGetOrdersResponse());

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

    expect(
        in_array([
            'level'   => 'debug',
            'message' => '[PDK]: Webhook processed',
            'context' => ['hook' => $expectedClass],
        ], $logs->toArray())
    )
        ->toBeTrue();
})->with([
    'shop subscription features updated' => [
        'hook'  => WebhookSubscription::SUBSCRIPTION_CREATED_OR_UPDATED,
        'class' => SubscriptionCreatedOrUpdatedWebhook::class,
        'body'  => [
            'id' => 1,
        ],
    ],
]);
