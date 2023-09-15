<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Request\Webhook;

use MyParcelNL\Pdk\App\Request\AbstractEndpointRequest;

class CreateWebhooksEndpointRequest extends AbstractEndpointRequest
{
    public function getMethod(): string
    {
        return 'POST';
    }

    public function getProperty(): string
    {
        return 'webhooks';
    }
}
