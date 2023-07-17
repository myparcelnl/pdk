<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Response;

use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\Account\Model\Acl;
use MyParcelNL\Pdk\Api\Response\ApiResponseWithBody;

class GetSubscriptionFeaturesResponse extends ApiResponseWithBody
{
    /**
     * @var array
     */
    private $subscriptionFeatures;

    /**
     * @return array
     */
    public function getSubscriptionFeatures(): array
    {
        return $this->subscriptionFeatures;
    }

    protected function parseResponseBody(): void
    {
        $data = json_decode($this->getBody(), true);

        $this->subscriptionFeatures = $data['data']['subscriptions'] ?? [];
    }
}
