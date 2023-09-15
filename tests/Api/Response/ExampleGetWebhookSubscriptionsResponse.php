<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

class ExampleGetWebhookSubscriptionsResponse extends ExampleJsonResponse
{
    /**
     * @return array[]
     */
    protected function getDefaultResponseContent(): array
    {
        return [
            [
                'hook' => 'christmas_tree',
                'url'  => 'https://example.com/webhook',
            ],
        ];
    }

    protected function getResponseProperty(): string
    {
        return 'webhook_subscriptions';
    }
}
