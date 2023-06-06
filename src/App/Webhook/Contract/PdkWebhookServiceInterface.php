<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Webhook\Contract;

/**
 * @see \MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhooksRepositoryInterface
 */
interface PdkWebhookServiceInterface
{
    /**
     * Generate a hashed url for webhook callbacks.
     */
    public function createUrl(): string;

    /**
     * The base url of all webhooks.
     */
    public function getBaseUrl(): string;
}
