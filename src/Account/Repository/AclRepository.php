<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Repository;

use MyParcelNL\Pdk\Account\Request\GetSubscriptionFeaturesRequest;
use MyParcelNL\Pdk\Account\Response\GetSubscriptionFeaturesResponse;
use MyParcelNL\Pdk\Base\Repository\ApiRepository;
use MyParcelNL\Pdk\Base\Support\Collection;

final class AclRepository extends ApiRepository
{
    /**
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    public function getSubscriptionFeatures(): Collection
    {
        return $this->retrieve('acl', function () {
            /** @var \MyParcelNL\Pdk\Account\Response\GetSubscriptionFeaturesResponse $response */
            $response = $this->api->doRequest(
                new GetSubscriptionFeaturesRequest(),
                GetSubscriptionFeaturesResponse::class
            );

            return $response->getSubscriptionFeatures();
        });
    }
}
