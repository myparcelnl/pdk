<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Webhook\Request;

use MyParcelNL\Pdk\Base\Request\Request;

class GetWebhookSubscriptionsRequest extends Request
{
    /**
     * @return string
     */
    public function getPath(): string
    {
        return '/webhook_subscriptions';
    }
}
