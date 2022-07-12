<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Response;

use MyParcelNL\Pdk\Api\Response\AbstractApiResponseWithBody;
use MyParcelNL\Pdk\Base\Collection;
use MyParcelNL\Sdk\src\Model\Consignment\PostNLConsignment;

class GetShipmentsResponse extends AbstractApiResponseWithBody
{
    /**
     * @var mixed
     */
    private $shipments;

    /**
     * @return \MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment[]|Collection
     */
    public function getShipments(): Collection
    {
        return $this->shipments;
    }

    protected function parseResponseBody(string $body): void
    {
        $parsedBody      = json_decode($body, true);
        $shipments       = $parsedBody['data']['shipments'] ?? [];
        $this->shipments = (new Collection($shipments))->map(
            function (array $data) {
                // TODO: make actual shipment class
                return new PostNLConsignment();
            }
        );
    }
}
