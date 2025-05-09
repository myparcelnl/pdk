<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Webhook\Repository;

use BadMethodCallException;
use Exception;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Tests\Api\Response\Example204NoContentResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetWebhookSubscriptionsResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePostIdsResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockApi;
use MyParcelNL\Pdk\Tests\Uses\UsesApiMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Tests\Uses\UsesMockEachLogger;
use MyParcelNL\Pdk\Webhook\Collection\WebhookSubscriptionCollection;
use MyParcelNL\Pdk\Webhook\Model\WebhookSubscription;
use ReflectionClass;
use function MyParcelNL\Pdk\Tests\usesShared;

uses()->group('webhook');

usesShared(new UsesMockPdkInstance(), new UsesApiMock(), new UsesMockEachLogger());

it('creates WebhookSubscription from api response', function () {
    MockApi::enqueue(new ExampleGetWebhookSubscriptionsResponse());

    /** @var \MyParcelNL\Pdk\Webhook\Repository\WebhookSubscriptionRepository $repository */
    $repository = Pdk::get(WebhookSubscriptionRepository::class);

    expect($repository->get(1))->toBeInstanceOf(WebhookSubscription::class);
});

it('creates WebhookSubscriptionCollection from api response', function () {
    MockApi::enqueue(new ExampleGetWebhookSubscriptionsResponse());

    /** @var \MyParcelNL\Pdk\Webhook\Repository\WebhookSubscriptionRepository $repository */
    $repository = Pdk::get(WebhookSubscriptionRepository::class);

    expect($repository->getAll())->toBeInstanceOf(WebhookSubscriptionCollection::class);
});

it('subscribes to a webhook', function () {
    MockApi::enqueue(new ExamplePostIdsResponse([['id' => 3001518]]));

    /** @var \MyParcelNL\Pdk\Webhook\Repository\WebhookSubscriptionRepository $repository */
    $repository = Pdk::get(WebhookSubscriptionRepository::class);

    $response = $repository->subscribe(
        new WebhookSubscription([
            'url'  => 'https://example.com/webhook',
            'hook' => WebhookSubscription::SHIPMENT_LABEL_CREATED,
        ])
    );

    expect($response)
        ->toBeInstanceOf(WebhookSubscription::class)
        ->and($response->toArray())
        ->toEqual([
            'id'   => 3001518,
            'hook' => WebhookSubscription::SHIPMENT_LABEL_CREATED,
            'url'  => 'https://example.com/webhook',
        ]);
});

it('subscribes to a webhook using a shorthand method', function () {
    MockApi::enqueue(new ExamplePostIdsResponse([['id' => 5731310]]));

    /** @var \MyParcelNL\Pdk\Webhook\Repository\WebhookSubscriptionRepository $repository */
    $repository = Pdk::get(WebhookSubscriptionRepository::class);

    $response = $repository->subscribeToOrderStatusChange('https://example.com/webhook');

    expect($response)
        ->toBeInstanceOf(WebhookSubscription::class)
        ->and($response->toArray())
        ->toEqual([
            'id'   => 5731310,
            'hook' => WebhookSubscription::ORDER_STATUS_CHANGE,
            'url'  => 'https://example.com/webhook',
        ]);
});

it('throws an exception when calling a non-existing method', function () {
    /** @var \MyParcelNL\Pdk\Webhook\Repository\WebhookSubscriptionRepository $repository */
    $repository = Pdk::get(WebhookSubscriptionRepository::class);

    /** @noinspection PhpUndefinedMethodInspection */
    $repository->someRandomMethod('https://example.com/webhook');
})->throws(BadMethodCallException::class);

it('unsubscribes from a webhook', function () {
    MockApi::enqueue(new Example204NoContentResponse());

    /** @var \MyParcelNL\Pdk\Webhook\Repository\WebhookSubscriptionRepository $repository */
    $repository = Pdk::get(WebhookSubscriptionRepository::class);

    expect($repository->unsubscribe(1))->toBeTrue();
});

it('returns true and logs warning if unsubscribe fails with deleteResourceOwnedByOthers error', function () {
    /** @var \MyParcelNL\Pdk\Webhook\Repository\WebhookSubscriptionRepository $repository */
    $repository = Pdk::get(WebhookSubscriptionRepository::class);

    $apiMock    = new class {
        public function doRequest()
        {
            throw new Exception('Permission Denied. (deleteResourceOwnedByOthers)');
        }
    };
    $reflection = new ReflectionClass($repository);
    $property   = $reflection->getProperty('api');
    $property->setAccessible(true);
    $property->setValue($repository, $apiMock);

    expect($repository->unsubscribe(123))->toBeTrue();

    $logs = Logger::getLogs();
    $log = reset($logs);

    expect($log)->toBe([
        'level'   => 'warning',
        'message' => '[PDK]: Could not delete webhook because it is owned by another shop',
        'context' => [
            'webhook_id' => 123,
            'error'      => 'Permission Denied. (deleteResourceOwnedByOthers)',
        ],
    ]);
});

it('throws other exceptions from unsubscribe', function () {
    /** @var \MyParcelNL\Pdk\Webhook\Repository\WebhookSubscriptionRepository $repository */
    $repository = Pdk::get(WebhookSubscriptionRepository::class);

    $apiMock    = new class {
        public function doRequest()
        {
            throw new Exception('Something');
        }
    };
    $reflection = new ReflectionClass($repository);
    $property   = $reflection->getProperty('api');
    $property->setAccessible(true);
    $property->setValue($repository, $apiMock);

    expect(fn() => $repository->unsubscribe(456))->toThrow(Exception::class, 'Something');
});
