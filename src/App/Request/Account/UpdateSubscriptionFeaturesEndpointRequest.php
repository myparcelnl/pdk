<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Request\Account;

use MyParcelNL\Pdk\App\Request\AbstractEndpointRequest;

final class UpdateSubscriptionFeaturesEndpointRequest extends AbstractEndpointRequest
{
    public function getMethod(): string
    {
        return 'GET';
    }

    public function getResponseProperty(): string
    {
        return 'data';
    }
}
