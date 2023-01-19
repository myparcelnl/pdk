<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Request\Webhook;

use MyParcelNL\Pdk\Plugin\Request\AbstractEndpointRequest;

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
