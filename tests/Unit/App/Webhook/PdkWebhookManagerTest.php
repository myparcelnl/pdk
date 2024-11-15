<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Webhook;

use MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhookManagerInterface;
use MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhooksRepositoryInterface;
use MyParcelNL\Pdk\App\Webhook\Hook\OrderStatusChangeWebhook;
use MyParcelNL\Pdk\App\Webhook\Hook\ShipmentLabelCreatedWebhook;
use MyParcelNL\Pdk\App\Webhook\Hook\ShipmentStatusChangeWebhook;
use MyParcelNL\Pdk\App\Webhook\Hook\ShopCarrierAccessibilityUpdatedWebhook;
use MyParcelNL\Pdk\App\Webhook\Hook\ShopCarrierConfigurationUpdatedWebhook;
use MyParcelNL\Pdk\App\Webhook\Hook\ShopUpdatedWebhook;
use MyParcelNL\Pdk\App\Webhook\Hook\SubscriptionCreatedOrUpdatedWebhook;
use MyParcelNL\Pdk\Base\Contract\CronServiceInterface;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Uses\UsesMockEachCron;
use MyParcelNL\Pdk\Tests\Uses\UsesMockEachLogger;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Webhook\Collection\WebhookSubscriptionCollection;
use MyParcelNL\Pdk\Webhook\Model\WebhookSubscription;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function MyParcelNL\Pdk\Tests\usesShared;

uses()->group('webhook');

usesShared(new UsesMockPdkInstance(), new UsesMockEachCron(), new UsesMockEachLogger());

/**
 * @param  string $url
 * @param  string $hook
 * @param  bool   $useHeader
 *
 * @return \Symfony\Component\HttpFoundation\Request
 */
function createWebhookRequest(string $url, string $hook, bool $useHeader = false): Request
{
    return Request::create(
        $url,
        Request::METHOD_POST,
        [],
        [],
        [],
        ['HTTP_X_MYPARCEL_HOOK' => $hook],
        json_encode([
            'data' => [
                'hooks' => [
                    $useHeader ? [] : ['event' => $hook],
                ],
            ],
        ])
    );
}

function sendWebhook(string $hook, bool $useHeader = false): Response
{
    /** @var \MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhooksRepositoryInterface $webhooksRepository */
    $webhooksRepository = Pdk::get(PdkWebhooksRepositoryInterface::class);
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdkWebhookManager $webhookManager */
    $webhookManager = Pdk::get(PdkWebhookManagerInterface::class);

    $subscriptions = new WebhookSubscriptionCollection([
        [
            'hook' => $hook,
            'url'  => $webhooksRepository->getHashedUrl(),
        ],
    ]);

    $webhooksRepository->storeHashedUrl('https://example.com/hashed-url');
    $webhooksRepository->store($subscriptions);

    return $webhookManager->call(createWebhookRequest($webhooksRepository->getHashedUrl(), $hook, $useHeader));
}

it('dispatches and executes webhooks', function (string $hook, string $calledClass, bool $useHeader) {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdkWebhookManager $webhookManager */
    $webhookManager = Pdk::get(PdkWebhookManagerInterface::class);
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockCronService $cronService */
    $cronService = Pdk::get(CronServiceInterface::class);

    $time     = time();
    $response = sendWebhook($hook, $useHeader);

    $scheduled = $cronService->getScheduledTasks();
    $timestamp = $scheduled->first()['timestamp'];

    expect($response->getStatusCode())
        ->toBe(Response::HTTP_ACCEPTED)
        ->and($response->getStatusCode())
        ->toBe(Response::HTTP_ACCEPTED)
        ->and($scheduled->all())
        ->toHaveLength(1)
        // The webhook is scheduled to start immediately. Add a little window to account for the time it takes to run the test.
        ->and($timestamp)
        ->toBeGreaterThanOrEqual($time - 10)
        ->and($timestamp)
        ->toBeLessThanOrEqual($time + 10)
        ->and($scheduled->first()['callback'])
        ->toBe([$webhookManager, 'processWebhook']);

    $cronService->executeScheduledTask();

    $calledHooks = $webhookManager->getCalledHooks();

    expect($calledHooks)
        ->toHaveLength(1)
        ->and(Arr::first($calledHooks))
        ->toBeInstanceOf($calledClass);
})
    ->with([
        WebhookSubscription::SHIPMENT_STATUS_CHANGE => [
            WebhookSubscription::SHIPMENT_STATUS_CHANGE,
            ShipmentStatusChangeWebhook::class,
        ],

        WebhookSubscription::SHIPMENT_LABEL_CREATED => [
            WebhookSubscription::SHIPMENT_LABEL_CREATED,
            ShipmentLabelCreatedWebhook::class,
        ],

        WebhookSubscription::ORDER_STATUS_CHANGE => [
            WebhookSubscription::ORDER_STATUS_CHANGE,
            OrderStatusChangeWebhook::class,
        ],

        WebhookSubscription::SHOP_UPDATED => [
            WebhookSubscription::SHOP_UPDATED,
            ShopUpdatedWebhook::class,
        ],

        WebhookSubscription::SHOP_CARRIER_ACCESSIBILITY_UPDATED => [
            WebhookSubscription::SHOP_CARRIER_ACCESSIBILITY_UPDATED,
            ShopCarrierAccessibilityUpdatedWebhook::class,
        ],

        WebhookSubscription::SHOP_CARRIER_CONFIGURATION_UPDATED => [
            WebhookSubscription::SHOP_CARRIER_CONFIGURATION_UPDATED,
            ShopCarrierConfigurationUpdatedWebhook::class,
        ],

        WebhookSubscription::SUBSCRIPTION_CREATED_OR_UPDATED => [
            WebhookSubscription::SUBSCRIPTION_CREATED_OR_UPDATED,
            SubscriptionCreatedOrUpdatedWebhook::class,
        ],
    ])
    ->with([
        'no header' => [false],
        'header'    => [true],
    ]);

it('throws error if webhook is not found', function () {
    /** @var \MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhooksRepositoryInterface $repository */
    $repository = Pdk::get(PdkWebhooksRepositoryInterface::class);
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdkWebhookManager $webhookManager */
    $webhookManager = Pdk::get(PdkWebhookManagerInterface::class);

    $repository->storeHashedUrl('https://example.com/hashed-url');

    $request = createWebhookRequest($repository->getHashedUrl(), 'unknown');

    $response = $webhookManager->call($request);

    expect($response->getStatusCode())->toBe(Response::HTTP_ACCEPTED);
});

it('does nothing if webhook is called with invalid context', function () {
    /** @var \MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhooksRepositoryInterface $repository */
    $repository = Pdk::get(PdkWebhooksRepositoryInterface::class);
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdkWebhookManager $webhookManager */
    $webhookManager = Pdk::get(PdkWebhookManagerInterface::class);
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockCronService $cronService */
    $cronService = Pdk::get(CronServiceInterface::class);

    $repository->storeHashedUrl('https://example.com/hashed-url');

    $request  = createWebhookRequest($repository->getHashedUrl(), 'unknown');
    $response = $webhookManager->call($request, 'invalid_context');

    $scheduledTasks = $cronService->getScheduledTasks();

    expect($response->getStatusCode())
        ->toBe(Response::HTTP_ACCEPTED)
        ->and($scheduledTasks)
        ->toBeEmpty();
});
