<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Repository;

use MyParcelNL\Pdk\Account\Request\GetCarrierOptionsRequest;
use MyParcelNL\Pdk\Account\Response\GetCarrierOptionsResponse;
use MyParcelNL\Pdk\Base\Repository\ApiRepository;
use MyParcelNL\Pdk\Carrier\Collection\CarrierOptionsCollection;

class CarrierOptionsRepository extends ApiRepository
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
            /** @var \MyParcelNL\Pdk\Account\Response\GetCarrierOptionsResponse $response */
            $response = $this->api->doRequest(
                new GetCarrierOptionsRequest($carrierId),
                GetCarrierOptionsResponse::class
            );

            return $response->getCarrierOptions();
        });
    }
}
