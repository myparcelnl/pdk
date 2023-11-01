<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

use MyParcelNL\Pdk\Webhook\Model\WebhookSubscription;

class ExampleGetWebhookSubscriptionsResponse extends ExampleJsonResponse
{
    /**
     * @return array[]
     */
    protected function getDefaultResponseContent(): array
    {
        return [
            [
                'id'         => 124567,
                'account_id' => 120,
                'shop_id'    => 2100,
                'hook'       => WebhookSubscription::SHIPMENT_LABEL_CREATED,
                'url'        => 'https://example.com/webhook',
            ],
        ];
    }

    /**
     * @return string
     */
    protected function getResponseProperty(): string
    {
        return 'webhook_subscriptions';
    }
}
