<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Webhook\Response;

use MyParcelNL\Pdk\Api\Response\ApiResponseWithBody;
use MyParcelNL\Pdk\Webhook\Collection\WebhookSubscriptionCollection;

class GetWebhookSubscriptionsResponse extends ApiResponseWithBody
{
    /**
     * @var \MyParcelNL\Pdk\Webhook\Collection\WebhookSubscriptionCollection
     */
    private $subscriptions;

    /**
     * @return \MyParcelNL\Pdk\Webhook\Collection\WebhookSubscriptionCollection
     */
    public function getSubscriptions(): WebhookSubscriptionCollection
    {
        return $this->subscriptions;
    }

    protected function parseResponseBody(): void
    {
        $parsedBody          = json_decode($this->getBody(), true);
        $this->subscriptions = new WebhookSubscriptionCollection($parsedBody['data']['webhook_subscriptions'] ?? []);
    }
}
