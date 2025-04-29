<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Webhook;

use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhooksRepositoryInterface;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Api\Response\Example204NoContentResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetWebhookSubscriptionsResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePostWebhooksResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockApi;
use MyParcelNL\Pdk\Tests\Uses\UsesApiMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Webhook\Model\WebhookSubscription;
use Symfony\Component\HttpFoundation\Request;
use function MyParcelNL\Pdk\Tests\usesShared;

uses()->group('webhook');

usesShared(new UsesMockPdkInstance(), new UsesApiMock());

function createWebhooks(array $options): void
{
    Actions::execute(new Request(array_replace(['action' => PdkBackendActions::CREATE_WEBHOOKS], $options)));
}

it('creates single webhook subscription', function () {
    MockApi::enqueue(
        new ExamplePostWebhooksResponse(),
        new ExampleGetWebhookSubscriptionsResponse()
    );

    createWebhooks(['hooks' => WebhookSubscription::SHIPMENT_STATUS_CHANGE]);

    /** @var \MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhooksRepositoryInterface $repo */
    $repo = Pdk::get(PdkWebhooksRepositoryInterface::class);

    $allWebhooks = $repo->getAll();
    $firstHook   = $allWebhooks->first();

    expect($allWebhooks)
        ->toHaveLength(1)
        ->and($firstHook->id)
        ->toBe(1)
        ->and($firstHook->hook)
        ->toBe(WebhookSubscription::SHIPMENT_STATUS_CHANGE)
        ->and($firstHook->url)
        ->toMatch('/^API\/webhook\/[a-z0-9]{32}$/');
});

it('creates multiple webhook subscriptions', function () {
    MockApi::enqueue(
        new ExamplePostWebhooksResponse(array_map(function (int $index) {
            return ['id' => $index + 1];
        }, array_keys(WebhookSubscription::ALL))),
        new ExampleGetWebhookSubscriptionsResponse()
    );

    Actions::execute(new Request([
            'action' => PdkBackendActions::CREATE_WEBHOOKS,
            'hooks'  => implode(',', WebhookSubscription::ALL),
        ])
    );

    /** @var \MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhooksRepositoryInterface $repo */
    $repo = Pdk::get(PdkWebhooksRepositoryInterface::class);

    $allWebhooks = $repo->getAll();

    expect($allWebhooks)->toHaveLength(count(WebhookSubscription::ALL));

    $allWebhooks->each(function (WebhookSubscription $subscription, int $index) {
        expect($subscription->id)
            ->toBe($index + 1)
            ->and($subscription->hook)
            ->toBe(WebhookSubscription::ALL[$index])
            ->and($subscription->url)
            ->toMatch('/^API\/webhook\/[a-z0-9]{32}$/');
    });
});

it('refreshes the url on each generation', function () {
    /** @var \MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhooksRepositoryInterface $repo */
    $repo = Pdk::get(PdkWebhooksRepositoryInterface::class);

    MockApi::enqueue(
        new ExamplePostWebhooksResponse(),
        new ExampleGetWebhookSubscriptionsResponse(),
        new ExamplePostWebhooksResponse(),
        new ExampleGetWebhookSubscriptionsResponse()
    );

    createWebhooks(['hooks' => WebhookSubscription::SHIPMENT_STATUS_CHANGE]);

    $oldWebhooks = $repo->getAll();
    $oldUrl      = $oldWebhooks->first()->url;

    createWebhooks(['hooks' => WebhookSubscription::SHIPMENT_STATUS_CHANGE]);

    $allWebhooks = $repo->getAll();

    expect($allWebhooks)
        ->toHaveLength(1)
        ->and($allWebhooks->first()->url)->not->toBe($oldUrl);
});

it('deletes all webhooks and clears local storage', function () {
    MockApi::enqueue(
        new ExampleGetWebhookSubscriptionsResponse(), // ophalen bestaande webhooks
        new Example204NoContentResponse(),            // verwijderen webhook 1
        new Example204NoContentResponse()             // verwijderen webhook 2
    );

    createWebhooks([
        'hooks' => implode(',', [
            WebhookSubscription::SHIPMENT_STATUS_CHANGE,
            WebhookSubscription::ORDER_STATUS_CHANGE,
        ]),
    ]);

    /** @var \MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhooksRepositoryInterface $repo */
    $repo = Pdk::get(PdkWebhooksRepositoryInterface::class);
    expect($repo->getAll())->not->toBeEmpty();

    Actions::execute(new Request(['action' => PdkBackendActions::DELETE_WEBHOOKS]));

    expect($repo->getAll())->toBeEmpty();
});
