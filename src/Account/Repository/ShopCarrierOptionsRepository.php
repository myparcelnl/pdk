<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Repository;

use MyParcelNL\Pdk\Account\Request\GetShopCarrierOptionsRequest;
use MyParcelNL\Pdk\Account\Response\GetShopCarrierOptionsResponse;
use MyParcelNL\Pdk\Base\Repository\ApiRepository;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;

class ShopCarrierOptionsRepository extends ApiRepository
{
    /**
     * @noinspection PhpUnused
     */
    public function getCarrierOptions(int $carrierId): CarrierCollection
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
