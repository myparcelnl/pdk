<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Webhook\Service;

final class MockPdkWebhookService extends AbstractPdkWebhookService
{
    public function getBaseUrl(): string
    {
        return 'test';
    }
}
