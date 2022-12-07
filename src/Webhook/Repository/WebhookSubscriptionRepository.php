<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Webhook\Repository;

use MyParcelNL\Pdk\Base\Repository\ApiRepository;
use MyParcelNL\Pdk\Base\Response\PostIdsResponse;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Webhook\Collection\WebhookSubscriptionCollection;
use MyParcelNL\Pdk\Webhook\Model\WebhookSubscription;
use MyParcelNL\Pdk\Webhook\Request\DeleteWebhookSubscriptionRequest;
use MyParcelNL\Pdk\Webhook\Request\GetWebhookSubscriptionRequest;
use MyParcelNL\Pdk\Webhook\Request\GetWebhookSubscriptionsRequest;
use MyParcelNL\Pdk\Webhook\Request\PostWebhookSubscriptionRequest;
use MyParcelNL\Pdk\Webhook\Response\GetWebhookSubscriptionsResponse;
use MyParcelNL\Sdk\src\Support\Str;

/**
 * @method Collection subscribeToOrderStatusChange(string $url)
 * @method Collection subscribeToShipmentLabelCreated(string $url)
 * @method Collection subscribeToShipmentStatusChange(string $url)
 * @method Collection subscribeToShopCarrierAccessibilityUpdated(string $url)
 * @method Collection subscribeToShopCarrierConfigurationUpdated(string $url)
 * @method Collection subscribeToShopUpdated(string $url)
 */
class WebhookSubscriptionRepository extends ApiRepository
{
    private const SHORTHAND_PREFIX = 'subscribeTo';

    /**
     * Support dynamic method calls to subscribe to a webhook.
     *
     * @param  mixed $name
     * @param  mixed $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (Str::startsWith($name, self::SHORTHAND_PREFIX)) {
            $hook = Str::snake(Str::replaceFirst(self::SHORTHAND_PREFIX, '', $name));
            return $this->subscribe($hook, $arguments[0]);
        }

        return $this->{$name}(...$arguments);
    }

    /**
     * Get a webhook subscription by id.
     *
     * @param  int $id
     *
     * @return \MyParcelNL\Pdk\Webhook\Model\WebhookSubscription
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
     *
     * @return \MyParcelNL\Pdk\Webhook\Collection\WebhookSubscriptionCollection
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
     * Subscribe to a webhook. This will create a new webhook subscription if it does not exist yet, or update the
     * existing one if it does. Does not validate whether the hook exists on purpose, so future hooks are supported
     * automatically.
     *
     * @param  string $hook
     * @param  string $url
     *
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    public function subscribe(string $hook, string $url): Collection
    {
        /** @var \MyParcelNL\Pdk\Base\Response\PostIdsResponse $response */
        $response = $this->api->doRequest(new PostWebhookSubscriptionRequest($hook, $url), PostIdsResponse::class);

        return $response->getIds();
    }

    /**
     * Unsubscribe from a webhook by id.
     *
     * @param  int $id
     *
     * @return void
     */
    public function unsubscribe(int $id): bool
    {
        return $this->api
            ->doRequest(new DeleteWebhookSubscriptionRequest($id))
            ->isOkResponse();
    }
}
