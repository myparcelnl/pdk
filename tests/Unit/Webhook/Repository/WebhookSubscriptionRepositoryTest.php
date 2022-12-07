<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Api\Service\ApiServiceInterface;
use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Api\Response\Example204NoContentResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetWebhookSubscriptionsResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePostIdsResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use MyParcelNL\Pdk\Webhook\Collection\WebhookSubscriptionCollection;
use MyParcelNL\Pdk\Webhook\Model\WebhookSubscription;
use MyParcelNL\Pdk\Webhook\Repository\WebhookSubscriptionRepository;

beforeEach(function () {
    $pdk = PdkFactory::create(MockPdkConfig::create());

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api = $pdk->get(ApiServiceInterface::class);

    $this->mock = $api->getMock();
});

afterEach(function () {
    $this->mock->reset();
});

it('creates WebhookSubscription from api response', function () {
    /** @var \MyParcelNL\Pdk\Webhook\Repository\WebhookSubscriptionRepository $repository */
    $repository = Pdk::get(WebhookSubscriptionRepository::class);

    $this->mock->append(new ExampleGetWebhookSubscriptionsResponse());

    expect($repository->get(1))->toBeInstanceOf(WebhookSubscription::class);
});

it('creates WebhookSubscriptionCollection from api response', function () {
    /** @var \MyParcelNL\Pdk\Webhook\Repository\WebhookSubscriptionRepository $repository */
    $repository = Pdk::get(WebhookSubscriptionRepository::class);

    $this->mock->append(new ExampleGetWebhookSubscriptionsResponse());

    expect($repository->getAll())->toBeInstanceOf(WebhookSubscriptionCollection::class);
});

it('subscribes to a webhook', function () {
    /** @var \MyParcelNL\Pdk\Webhook\Repository\WebhookSubscriptionRepository $repository */
    $repository = Pdk::get(WebhookSubscriptionRepository::class);

    $this->mock->append(new ExamplePostIdsResponse(['id' => 3001518]));

    $response = $repository->subscribe(WebhookSubscription::ORDER_STATUS_CHANGE, 'https://example.com/webhook');

    expect($response)
        ->toBeInstanceOf(Collection::class)
        ->and($response->toArray())
        ->toEqual(['id' => 3001518]);
});

it('subscribes to a webhook using a shorthand method', function () {
    /** @var \MyParcelNL\Pdk\Webhook\Repository\WebhookSubscriptionRepository $repository */
    $repository = Pdk::get(WebhookSubscriptionRepository::class);

    $this->mock->append(new ExamplePostIdsResponse(['id' => 5731310]));

    $response = $repository->subscribeToOrderStatusChange('https://example.com/webhook');

    expect($response)
        ->toBeInstanceOf(Collection::class)
        ->and($response->toArray())
        ->toEqual(['id' => 5731310]);
});

it('throws an exception when calling a non-existing method', function () {
    /** @var \MyParcelNL\Pdk\Webhook\Repository\WebhookSubscriptionRepository $repository */
    $repository = Pdk::get(WebhookSubscriptionRepository::class);

    $repository->someRandomMethod('https://example.com/webhook');
})->throws(BadMethodCallException::class);

it('unsubscribes from a webhook', function () {
    /** @var \MyParcelNL\Pdk\Webhook\Repository\WebhookSubscriptionRepository $repository */
    $repository = Pdk::get(WebhookSubscriptionRepository::class);

    $this->mock->append(new Example204NoContentResponse());

    expect($repository->unsubscribe(1))->toBeTrue();
});
