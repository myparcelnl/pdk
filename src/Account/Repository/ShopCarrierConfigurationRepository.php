<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Repository;

use MyParcelNL\Pdk\Account\Model\ShopCarrierConfiguration;
use MyParcelNL\Pdk\Account\Request\GetShopCarrierConfigurationRequest;
use MyParcelNL\Pdk\Account\Request\GetShopCarrierConfigurationsRequest;
use MyParcelNL\Pdk\Account\Response\GetShopCarrierConfigurationsResponse;
use MyParcelNL\Pdk\Base\Repository\ApiRepository;
use MyParcelNL\Pdk\Base\Support\Collection;

class ShopCarrierConfigurationRepository extends ApiRepository
{
    /**
     * @noinspection PhpUnused
     */
    public function getCarrierConfiguration(int $shopId, string $carrier): ShopCarrierConfiguration
    {
        return $this->retrieve("shop_carrier_configuration_$carrier", function () use ($carrier, $shopId) {
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
     * @noinspection PhpUnused
     */
    public function getCarrierConfigurations(int $shopId): Collection
    {
        return $this->retrieve('shop_carrier_configurations', function () use ($shopId) {
            /** @var \MyParcelNL\Pdk\Account\Response\GetShopCarrierConfigurationsResponse $response */
            $response = $this->api->doRequest(
                new GetShopCarrierConfigurationsRequest($shopId),
                GetShopCarrierConfigurationsResponse::class
            );

            $collection = $response->getCarrierConfigurations();

            foreach ($collection->all() as $config) {
                $this->save(sprintf('shop_carrier_configuration_%s', $config->carrier), $config);
            }

            return $collection;
        });
    }
}
