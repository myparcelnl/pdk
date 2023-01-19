<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Repository;

use MyParcelNL\Pdk\Account\Request\GetShopCarrierOptionsRequest;
use MyParcelNL\Pdk\Account\Response\GetShopCarrierOptionsResponse;
use MyParcelNL\Pdk\Base\Repository\ApiRepository;
use MyParcelNL\Pdk\Carrier\Collection\CarrierOptionsCollection;

class ShopCarrierOptionsRepository extends ApiRepository
{
    /**
     * @param  int $carrierId
     *
     * @return \MyParcelNL\Pdk\Carrier\Collection\CarrierOptionsCollection
     * @noinspection PhpUnused
     */
    public function getCarrierOptions(int $carrierId): CarrierOptionsCollection
    {
        return $this->retrieve('carrier_options', function () use ($carrierId) {
            /** @var \MyParcelNL\Pdk\Account\Response\GetShopCarrierOptionsResponse $response */
            $response = $this->api->doRequest(
                new GetShopCarrierOptionsRequest($carrierId),
                GetShopCarrierOptionsResponse::class
            );

            return $response->getCarrierOptions();
        });
    }
}
