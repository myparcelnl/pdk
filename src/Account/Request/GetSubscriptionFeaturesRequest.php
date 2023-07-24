<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Request;

use MyParcelNL\Pdk\Api\Request\Request;

class GetSubscriptionFeaturesRequest extends Request
{
    /**
     * @return string
     */
    public function getPath(): string
    {
        return '/acl';
    }

    public function getResponseProperty(): ?string
    {
        return 'subscription_features';
    }
}
