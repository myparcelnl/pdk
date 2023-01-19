<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Request\Webhook;

use MyParcelNL\Pdk\Plugin\Request\AbstractEndpointRequest;

class DeleteWebhooksEndpointRequest extends AbstractEndpointRequest
{
    /**
     * @return string
     */
    public function getMethod(): string
    {
        return 'POST';
    }

    /**
     * @return string
     */
    public function getProperty(): string
    {
        return 'webhooks';
    }
}
