<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Response;

use MyParcelNL\Pdk\Api\Response\AbstractApiResponseWithBody;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Concern\HasDecodesShipment;

class GetShipmentsResponse extends AbstractApiResponseWithBody
{
    use HasDecodesShipment;

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
     * @param  string $body
     *
     * @return void
     * @throws \Exception
     */
    protected function parseResponseBody(string $body): void
    {
        $parsedBody = json_decode($body, true);
        $shipments  = $parsedBody['data']['shipments'] ?? [];

        $shipmentData = [];

        foreach ($shipments as $shipment) {
            $shipmentData[] = $this->decodeShipment($shipment);
        }

        $this->shipments = (new ShipmentCollection($shipmentData));
    }
}
