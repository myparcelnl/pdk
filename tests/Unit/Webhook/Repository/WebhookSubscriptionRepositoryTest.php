<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Api\Contract\ApiServiceInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Api\Response\Example204NoContentResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetWebhookSubscriptionsResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePostIdsResponse;
use MyParcelNL\Pdk\Tests\Uses\UsesApiMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Webhook\Collection\WebhookSubscriptionCollection;
use MyParcelNL\Pdk\Webhook\Model\WebhookSubscription;
use MyParcelNL\Pdk\Webhook\Repository\WebhookSubscriptionRepository;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(
    new UsesMockPdkInstance(),
    new UsesApiMock()
);

it('creates WebhookSubscription from api response', function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api = Pdk::get(ApiServiceInterface::class);
    $api->getMock()
        ->append(new ExampleGetWebhookSubscriptionsResponse());

    /** @var \MyParcelNL\Pdk\Webhook\Repository\WebhookSubscriptionRepository $repository */
    $repository = Pdk::get(WebhookSubscriptionRepository::class);

    expect($repository->get(1))->toBeInstanceOf(WebhookSubscription::class);
});

it('creates WebhookSubscriptionCollection from api response', function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api = Pdk::get(ApiServiceInterface::class);
    $api->getMock()
        ->append(new ExampleGetWebhookSubscriptionsResponse());

    /** @var \MyParcelNL\Pdk\Webhook\Repository\WebhookSubscriptionRepository $repository */
    $repository = Pdk::get(WebhookSubscriptionRepository::class);

    expect($repository->getAll())->toBeInstanceOf(WebhookSubscriptionCollection::class);
});

it('subscribes to a webhook', function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api = Pdk::get(ApiServiceInterface::class);
    $api->getMock()
        ->append(new ExamplePostIdsResponse([['id' => 3001518]]));

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
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api = Pdk::get(ApiServiceInterface::class);
    $api->getMock()
        ->append(new ExamplePostIdsResponse([['id' => 5731310]]));

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
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api = Pdk::get(ApiServiceInterface::class);
    $api->getMock()
        ->append(new Example204NoContentResponse());

    /** @var \MyParcelNL\Pdk\Webhook\Repository\WebhookSubscriptionRepository $repository */
    $repository = Pdk::get(WebhookSubscriptionRepository::class);

    expect($repository->unsubscribe(1))->toBeTrue();
});
