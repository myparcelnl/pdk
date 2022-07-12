<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Repository;

use MyParcelNL\Pdk\Account\Request\GetShopCarrierConfigurationRequest;
use MyParcelNL\Pdk\Account\Request\GetShopCarrierConfigurationsRequest;
use MyParcelNL\Pdk\Account\Response\GetShopCarrierConfigurationsResponseWithBody;
use MyParcelNL\Pdk\Base\Collection;
use MyParcelNL\Pdk\Base\Repository\AbstractRepository;
use MyParcelNL\Sdk\src\Model\Account\CarrierConfiguration;

class ShopCarrierConfigurationRepository extends AbstractRepository
{
    /**
     * @param  int    $shopId
     * @param  string $carrier
     *
     * @return \MyParcelNL\Sdk\src\Model\Account\CarrierConfiguration
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \MyParcelNL\Sdk\src\Exception\ApiException
     */
    public function getCarrierConfiguration(int $shopId, string $carrier): CarrierConfiguration
    {
        return $this->retrieve('carrier_configurations', function () use ($carrier, $shopId) {
            /** @var \MyParcelNL\Pdk\Account\Response\GetShopCarrierConfigurationsResponseWithBody $response */
            $response = $this->api->doRequest(
                new GetShopCarrierConfigurationRequest($shopId, $carrier),
                GetShopCarrierConfigurationsResponseWithBody::class
            );

            return $response
                ->getCarrierConfigurations()
                ->first();
        });
    }

    /**
     * @param  int $shopId
     *
     * @return \MyParcelNL\Pdk\Base\Collection
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \MyParcelNL\Sdk\src\Exception\ApiException
     */
    public function getCarrierConfigurations(int $shopId): Collection
    {
        return $this->retrieve('carrier_configurations', function () use ($shopId) {
            /** @var \MyParcelNL\Pdk\Account\Response\GetShopCarrierConfigurationsResponseWithBody $response */
            $response = $this->api->doRequest(
                new GetShopCarrierConfigurationsRequest($shopId),
                GetShopCarrierConfigurationsResponseWithBody::class
            );

            return $response->getCarrierConfigurations();
        });
    }
}
