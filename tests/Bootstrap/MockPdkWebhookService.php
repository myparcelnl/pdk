<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\App\Webhook\Service\AbstractPdkWebhookService;

final class MockPdkWebhookService extends AbstractPdkWebhookService
{
    public function getBaseUrl(): string
    {
        return 'API/webhook';
    }
}
