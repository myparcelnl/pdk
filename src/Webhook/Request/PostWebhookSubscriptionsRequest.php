<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Webhook\Request;

use MyParcelNL\Pdk\Base\Request\Request;
use MyParcelNL\Pdk\Webhook\Collection\WebhookSubscriptionCollection;

class PostWebhookSubscriptionsRequest extends Request
{
    /**
     * @var \MyParcelNL\Pdk\Webhook\Collection\WebhookSubscriptionCollection
     */
    private $collection;

    /**
     * @param  \MyParcelNL\Pdk\Webhook\Collection\WebhookSubscriptionCollection $collection
     */
    public function __construct(WebhookSubscriptionCollection $collection)
    {
        parent::__construct();
        $this->collection = $collection;
    }

    /**
     * @return string
     */
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

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return 'POST';
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return '/webhook_subscriptions';
    }
}
