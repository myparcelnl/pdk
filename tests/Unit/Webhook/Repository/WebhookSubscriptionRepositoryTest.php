<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Webhook\Repository;

use BadMethodCallException;
use MyParcelNL\Pdk\Base\Facade\MockApi;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Mock\Api\Response\Example204NoContentResponse;
use MyParcelNL\Pdk\Mock\Api\Response\ExampleGetWebhookSubscriptionsResponse;
use MyParcelNL\Pdk\Mock\Api\Response\ExamplePostIdsResponse;
use MyParcelNL\Pdk\Webhook\Collection\WebhookSubscriptionCollection;
use MyParcelNL\Pdk\Webhook\Model\WebhookSubscription;

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
