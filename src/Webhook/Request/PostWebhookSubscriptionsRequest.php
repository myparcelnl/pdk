<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Webhook\Request;

use MyParcelNL\Pdk\Api\Request\Request;
use MyParcelNL\Pdk\Webhook\Collection\WebhookSubscriptionCollection;

class PostWebhookSubscriptionsRequest extends Request
{
    public function __construct(private readonly WebhookSubscriptionCollection $collection)
    {
        parent::__construct();
    }

    public function getBody(): string
    {
        $array = [];

        foreach ($this->collection->all() as $subscription) {
            $array[] = [
                'hook' => $subscription->hook,
                'url'  => $subscription->url,
            ];
        }

        return json_encode([
            'data' => [
                'webhook_subscriptions' => $array,
            ],
        ]);
    }

    public function getMethod(): string
    {
        return 'POST';
    }

    public function getPath(): string
    {
        return '/webhook_subscriptions';
    }
}
