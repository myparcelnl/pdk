<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Repository;

use MyParcelNL\Pdk\Account\Request\GetShopCarrierConfigurationsRequest;
use MyParcelNL\Pdk\Account\Response\GetShopCarrierConfigurationsResponse;
use MyParcelNL\Pdk\Repository\AbstractRepository;
use MyParcelNL\Sdk\src\Support\Collection;

class ShopCarrierConfigurationRepository extends AbstractRepository
{
    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getCarrierConfigurations(int $shopId): Collection
    {
        return $this->retrieve('carrier_configurations', function () use ($shopId) {
            /** @var \MyParcelNL\Pdk\Account\Response\GetShopCarrierConfigurationsResponse $response */
            $response = $this->api->doRequest(
                new GetShopCarrierConfigurationsRequest($shopId),
                GetShopCarrierConfigurationsResponse::class
            );

            return $response->getCarrierConfigurations();
        });
    }
}
