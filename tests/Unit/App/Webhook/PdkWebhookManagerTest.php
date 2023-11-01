<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Webhook;

use MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhookManagerInterface;
use MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhooksRepositoryInterface;
use MyParcelNL\Pdk\Base\Contract\CronServiceInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Uses\UsesMockEachCron;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Webhook\Collection\WebhookSubscriptionCollection;
use MyParcelNL\Pdk\Webhook\Model\WebhookSubscription;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function MyParcelNL\Pdk\Tests\usesShared;

uses()->group('webhook');

usesShared(new UsesMockPdkInstance(), new UsesMockEachCron());

it('dispatches incoming webhook', function (string $hook) {
    /** @var PdkWebhooksRepositoryInterface $repository */
    $repository = Pdk::get(PdkWebhooksRepositoryInterface::class);
    /** @var PdkWebhookManagerInterface $webhookManager */
    $webhookManager = Pdk::get(PdkWebhookManagerInterface::class);
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockCronService $cronService */
    $cronService = Pdk::get(CronServiceInterface::class);

    $repository->store(new WebhookSubscriptionCollection([['hook' => $hook, 'url' => $repository->getHashedUrl()]]));

    $time     = time();
    $response = $webhookManager->call(new Request());

    $scheduled = $cronService->getScheduledTasks();

    $timestamp = $scheduled->first()['timestamp'];

    expect($response->getContent())
        ->toBe('')
        ->and($response->getStatusCode())
        ->toBe(Response::HTTP_ACCEPTED)
        ->and($scheduled->all())
        ->toHaveLength(1)
        // The webhook is scheduled to start immediately. Add a little window to account for the time it takes to run the test.
        ->and($timestamp)
        ->toBeGreaterThanOrEqual($time - 10)
        ->and($timestamp)
        ->toBeLessThanOrEqual($time + 10);
})->with(WebhookSubscription::ALL);
