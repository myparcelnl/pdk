<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Webhook\Repository;

use BadMethodCallException;
use MyParcelNL\Pdk\Api\Response\PostIdsResponse;
use MyParcelNL\Pdk\Base\Repository\ApiRepository;
use MyParcelNL\Pdk\Webhook\Collection\WebhookSubscriptionCollection;
use MyParcelNL\Pdk\Webhook\Model\WebhookSubscription;
use MyParcelNL\Pdk\Webhook\Request\DeleteWebhookSubscriptionRequest;
use MyParcelNL\Pdk\Webhook\Request\GetWebhookSubscriptionRequest;
use MyParcelNL\Pdk\Webhook\Request\GetWebhookSubscriptionsRequest;
use MyParcelNL\Pdk\Webhook\Request\PostWebhookSubscriptionsRequest;
use MyParcelNL\Pdk\Webhook\Response\GetWebhookSubscriptionsResponse;
use MyParcelNL\Sdk\src\Support\Str;

/**
 * @method WebhookSubscription subscribeToOrderStatusChange(string $url)
 * @method WebhookSubscription subscribeToShipmentLabelCreated(string $url)
 * @method WebhookSubscription subscribeToShipmentStatusChange(string $url)
 * @method WebhookSubscription subscribeToShopCarrierAccessibilityUpdated(string $url)
 * @method WebhookSubscription subscribeToShopCarrierConfigurationUpdated(string $url)
 * @method WebhookSubscription subscribeToShopUpdated(string $url)
 */
class WebhookSubscriptionRepository extends ApiRepository
{
    private const SHORTHAND_PREFIX = 'subscribeTo';

    /**
     * Support dynamic method calls to subscribe to a webhook.
     *
     * @return WebhookSubscription
     */
    public function __call(mixed $name, mixed $arguments)
    {
        if (! Str::startsWith($name, self::SHORTHAND_PREFIX)) {
            throw new BadMethodCallException("Method $name does not exist.");
        }

        $hook = Str::snake(Str::replaceFirst(self::SHORTHAND_PREFIX, '', $name));

        return $this->subscribe(new WebhookSubscription(['hook' => $hook, 'url' => $arguments[0]]));
    }

    /**
     * Get a webhook subscription by id.
     */
    public function get(int $id): WebhookSubscription
    {
        return $this->retrieve('webhook_subscription_' . $id, function () use ($id) {
            /** @var \MyParcelNL\Pdk\Webhook\Response\GetWebhookSubscriptionsResponse $response */
            $response = $this->api->doRequest(
                new GetWebhookSubscriptionRequest($id),
                GetWebhookSubscriptionsResponse::class
            );

            return $response->getSubscriptions()
                ->first();
        });
    }

    /**
     * Retrieve all existing webhook subscriptions.
     */
    public function getAll(): WebhookSubscriptionCollection
    {
        return $this->retrieve('webhook_subscriptions', function () {
            /** @var \MyParcelNL\Pdk\Webhook\Response\GetWebhookSubscriptionsResponse $response */
            $response = $this->api->doRequest(
                new GetWebhookSubscriptionsRequest(),
                GetWebhookSubscriptionsResponse::class
            );

            return $response->getSubscriptions();
        });
    }

    /**
     * Subscribe to a single webhook.
     *
     * @see \MyParcelNL\Pdk\Webhook\Repository\WebhookSubscriptionRepository::subscribeMany()
     */
    public function subscribe(WebhookSubscription $subscription): WebhookSubscription
    {
        return $this->subscribeMany(new WebhookSubscriptionCollection([$subscription]))
            ->first();
    }

    /**
     * Subscribe to multiple webhooks. This will create new webhook subscriptions if they do not exist yet, or update
     * the existing ones if they do. Does not validate whether the hook exists on purpose, so future hooks are
     * supported automatically.
     */
    public function subscribeMany(WebhookSubscriptionCollection $subscriptions): WebhookSubscriptionCollection
    {
        /** @var \MyParcelNL\Pdk\Api\Response\PostIdsResponse $response */
        $response = $this->api->doRequest(new PostWebhookSubscriptionsRequest($subscriptions), PostIdsResponse::class);

        return $subscriptions->addIds($response->getIds());
    }

    /**
     * Unsubscribe from a webhook by id.
     */
    public function unsubscribe(int $id): bool
    {
        return $this->api
            ->doRequest(new DeleteWebhookSubscriptionRequest($id))
            ->isOkResponse();
    }
}
