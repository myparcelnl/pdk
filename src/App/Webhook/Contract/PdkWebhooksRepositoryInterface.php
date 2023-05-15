<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Webhook\Contract;

use MyParcelNL\Pdk\Webhook\Collection\WebhookSubscriptionCollection;
use MyParcelNL\Pdk\Webhook\Model\WebhookSubscription;

interface PdkWebhooksRepositoryInterface
{
    public function get(string $hook): ?WebhookSubscription;

    public function getAll(): WebhookSubscriptionCollection;

    public function getHashedUrl(): ?string;

    public function has(string $hook): bool;

    public function remove(string $hook): void;

    public function store(WebhookSubscriptionCollection $subscriptions): void;

    public function storeHashedUrl(string $url): void;
}
