<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Request\Account;

use MyParcelNL\Pdk\App\Request\AbstractEndpointRequest;

final class UpdateSubscriptionFeaturesEndpointRequest extends AbstractEndpointRequest
{
    /**
     * @return string
     */
    public function getMethod(): string
    {
        return 'GET';
    }

    /**
     * @return string
     */
    public function getResponseProperty(): string
    {
        return 'data';
    }
}
