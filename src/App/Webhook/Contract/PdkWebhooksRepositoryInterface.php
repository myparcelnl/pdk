<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Webhook\Contract;

use MyParcelNL\Pdk\Base\Contract\RepositoryInterface;
use MyParcelNL\Pdk\Webhook\Collection\WebhookSubscriptionCollection;
use MyParcelNL\Pdk\Webhook\Model\WebhookSubscription;

/**
 * The webhook repository is responsible for storing and retrieving webhook subscriptions.
 * A hashed url will be generated and stored in the app. This url is used for webhook callbacks.
 *
 * @see \MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhookServiceInterface
 */
interface PdkWebhooksRepositoryInterface extends RepositoryInterface
{
    /**
     * Get a webhook subscription.
     */
    public function get(string $hook): ?WebhookSubscription;

    /**
     * Get all webhook subscriptions.
     */
    public function getAll(): WebhookSubscriptionCollection;

    /**
     * Get the hashed webhook url.
     */
    public function getHashedUrl(): ?string;

    /**
     * Check if a webhook subscription exists.
     */
    public function has(string $hook): bool;

    /**
     * Remove a webhook subscription.
     */
    public function remove(string $hook): void;

    /**
     * Store a webhook subscription in the app.
     */
    public function store(WebhookSubscriptionCollection $subscriptions): void;

    /**
     * Store the hashed webhook url in the app.
     */
    public function storeHashedUrl(string $url): void;
}
