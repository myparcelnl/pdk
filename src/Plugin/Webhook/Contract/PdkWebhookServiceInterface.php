<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Webhook\Contract;

interface PdkWebhookServiceInterface
{
    /**
     * The base url of all webhooks.
     */
    public function getBaseUrl(): string;

    /**
     * Generate a full url for a webhook.
     */
    public function createUrl(): string;
}