<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Repository;

use MyParcelNL\Pdk\Account\Request\GetCarrierOptionsRequest;
use MyParcelNL\Pdk\Account\Response\GetCarrierOptionsResponseWithBody;
use MyParcelNL\Pdk\Base\Repository\AbstractRepository;
use MyParcelNL\Pdk\Base\Support\Collection;

class CarrierOptionsRepository extends AbstractRepository
{
    /**
     * @param  int $carrierId
     *
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     * @noinspection PhpUnused
     */
    public function getCarrierOptions(int $carrierId): Collection
    {
        return $this->retrieve('carrier_options', function () use ($carrierId) {
            /** @var \MyParcelNL\Pdk\Account\Response\GetCarrierOptionsResponseWithBody $response */
            $response = $this->api->doRequest(
                new GetCarrierOptionsRequest($carrierId),
                GetCarrierOptionsResponseWithBody::class
            );

            return $response->getCarrierOptions();
        });
    }
}
