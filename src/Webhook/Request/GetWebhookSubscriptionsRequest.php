<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Webhook\Request;

use MyParcelNL\Pdk\Api\Request\Request;

class GetWebhookSubscriptionsRequest extends Request
{
    public function getPath(): string
    {
        return '/webhook_subscriptions';
    }
}
