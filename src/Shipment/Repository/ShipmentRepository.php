<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Repository;

use MyParcelNL\Pdk\Base\Repository\AbstractRepository;
use MyParcelNL\Pdk\Shipment\Request\GetShipmentsRequest;
use MyParcelNL\Pdk\Shipment\Response\GetShipmentsResponse;
use MyParcelNL\Sdk\src\Support\Collection;

class ShipmentRepository extends AbstractRepository
{
    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \MyParcelNL\Sdk\src\Exception\ApiException
     */
    public function getShipments(string $referenceIdentifier = null): Collection
    {
        return $this->retrieve('shop', function () use ($referenceIdentifier) {
            /** @var \MyParcelNL\Pdk\Shipment\Response\GetShipmentsResponse $response */
            $response = $this->api->doRequest(
                new GetShipmentsRequest($referenceIdentifier),
                GetShipmentsResponse::class
            );

            return $response->getShipments();
        });
    }
}
