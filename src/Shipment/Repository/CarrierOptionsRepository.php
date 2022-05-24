<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Repository;

use MyParcelNL\Pdk\Account\Request\GetShopCarrierConfigurationsRequest;
use MyParcelNL\Pdk\Account\Response\GetShopCarrierConfigurationsResponse;
use MyParcelNL\Pdk\Repository\AbstractRepository;
use MyParcelNL\Pdk\Shipment\Request\GetCarrierOptionsRequest;
use MyParcelNL\Pdk\Shipment\Response\GetCarrierOptionsResponse;
use MyParcelNL\Sdk\src\Support\Collection;

class CarrierOptionsRepository extends AbstractRepository
{
    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getCarrierOptions(int $carrierId): Collection
    {
        return $this->retrieve('carrier_options', function () use ($carrierId) {
            /** @var \MyParcelNL\Pdk\Shipment\Response\GetCarrierOptionsResponse $response */
            $response = $this->api->doRequest(
                new GetCarrierOptionsRequest($carrierId),
                GetCarrierOptionsResponse::class
            );

            return $response->getCarrierOptions();
        });
    }
}
