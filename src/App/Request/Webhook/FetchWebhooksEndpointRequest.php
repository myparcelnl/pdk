<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Request\Webhook;

use MyParcelNL\Pdk\App\Request\AbstractEndpointRequest;

class FetchWebhooksEndpointRequest extends AbstractEndpointRequest
{
    /**
     * @return string
     */
    public function getProperty(): string
    {
        return 'webhooks';
    }
}
