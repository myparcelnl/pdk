<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Repository;

use MyParcelNL\Pdk\Account\Request\GetShopCarrierConfigurationRequest;
use MyParcelNL\Pdk\Account\Request\GetShopCarrierConfigurationsRequest;
use MyParcelNL\Pdk\Account\Response\GetShopCarrierConfigurationsResponse;
use MyParcelNL\Pdk\Base\Repository\ApiRepository;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Sdk\src\Model\Account\CarrierConfiguration;

class ShopCarrierConfigurationRepository extends ApiRepository
{
    /**
     * @param  int    $shopId
     * @param  string $carrier
     *
     * @return \MyParcelNL\Sdk\src\Model\Account\CarrierConfiguration
     * @noinspection PhpUnused
     */
    public function getCarrierConfiguration(int $shopId, string $carrier): CarrierConfiguration
    {
        return $this->retrieve('carrier_configurations', function () use ($carrier, $shopId) {
            /** @var \MyParcelNL\Pdk\Account\Response\GetShopCarrierConfigurationsResponse $response */
            $response = $this->api->doRequest(
                new GetShopCarrierConfigurationRequest($shopId, $carrier),
                GetShopCarrierConfigurationsResponse::class
            );

            return $response
                ->getCarrierConfigurations()
                ->first();
        });
    }

    /**
     * @param  int $shopId
     *
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     * @noinspection PhpUnused
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
