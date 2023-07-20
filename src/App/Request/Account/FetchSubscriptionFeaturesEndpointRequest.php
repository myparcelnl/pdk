<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Request\Account;

use MyParcelNL\Pdk\App\Request\AbstractEndpointRequest;

class FetchSubscriptionFeaturesEndpointRequest extends AbstractEndpointRequest
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
    public function getProperty(): string
    {
        return 'subscription_features';
    }

    /**
     * @return string
     */
    public function getResponseProperty(): string
    {
        return 'context';
    }
}
