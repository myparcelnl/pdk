<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Webhook\Contract;

interface PdkWebhookServiceInterface
{
    /**
     * Generate a full url for a webhook.
     */
    public function createUrl(): string;

    /**
     * The base url of all webhooks.
     */
    public function getBaseUrl(): string;
}
