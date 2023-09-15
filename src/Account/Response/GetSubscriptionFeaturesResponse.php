<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Response;

use MyParcelNL\Pdk\Api\Response\ApiResponseWithBody;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Base\Support\Collection;

class GetSubscriptionFeaturesResponse extends ApiResponseWithBody
{
    private ?Collection $subscriptionFeatures = null;

    public function getSubscriptionFeatures(): Collection
    {
        return $this->subscriptionFeatures;
    }

    protected function parseResponseBody(): void
    {
        $data = json_decode($this->getBody(), true);

        $this->subscriptionFeatures = new Collection(Arr::get($data, 'subscription_features', []));
    }
}
