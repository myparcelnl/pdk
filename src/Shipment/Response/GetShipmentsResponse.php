<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Response;

use MyParcelNL\Pdk\Api\Response\ApiResponseWithBody;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Concern\DecodesRequestShipment;

class GetShipmentsResponse extends ApiResponseWithBody
{
    use DecodesRequestShipment;

    /**
     * @var \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection
     */
    private $shipments;

    /**
     * @return \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection
     */
    public function getShipments(): ShipmentCollection
    {
        return $this->shipments;
    }

    /**
     * @return void
     * @throws \Exception
     */
    protected function parseResponseBody(): void
    {
        $parsedBody = json_decode($this->getBody(), true);
        $shipments  = $parsedBody['data']['shipments'] ?? [];

        $this->shipments = new ShipmentCollection(array_map([$this, 'decodeShipment'], $shipments));
    }
}
