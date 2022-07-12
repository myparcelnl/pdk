<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Response;

use MyParcelNL\Pdk\Api\Response\AbstractApiResponseWithBody;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Sdk\src\Support\Arr;

class GetShipmentsResponse extends AbstractApiResponseWithBody
{
    /**
     * @var mixed
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
            $shipmentData[] = $this->createShipmentFromApiData($shipment);
        }

        $this->shipments = (new ShipmentCollection($shipmentData));
    }

    /**
     * @param  array $data
     *
     * @return \MyParcelNL\Pdk\Shipment\Model\Shipment
     * @throws \Exception
     */
    private function createShipmentFromApiData(array $data): Shipment
    {
        $isReturn = in_array((int) $data['shipment_type'], Shipment::RETURN_SHIPMENT_TYPES, true);

        $options            = $data['options'] ?? [];
        $physicalProperties = $data['physical_properties'] ?? [];

        return new Shipment([
            'id'                 => $data['id'],
            'carrier'            => ['subscriptionId' => $data['contract_id'], 'id' => $data['carrier_id']],
            'status'             => $data['status'],
            'barcode'            => $data['barcode'],
            'isReturn'           => $isReturn,
            'recipient'          => $this->filter($data['recipient']),
            'sender'             => $this->filter($data['sender']),
            'deliveryOptions'    => [
                'deliveryType'    => $options['delivery_type'],
                'packageType'     => $options['package_type'],
                'shipmentOptions' => $this->getShipmentOptions($options),
                'pickupLocation'  => $this->filter($data['pickup']),
            ],
            'dropOffPoint'       => $this->filter($data['drop_off_point']),
            'customsDeclaration' => $this->filter($data['customs_declaration']),
            'physicalProperties' => $physicalProperties
                ? Arr::only($physicalProperties, ['height', 'length', 'weight', 'width'])
                : null,
        ]);
    }

    /**
     * @param  null|array $item
     *
     * @return null|array
     */
    private function filter(?array $item): ?array
    {
        return array_filter($item ?? []) ?: null;
    }

    /**
     * @param  array $options
     *
     * @return array
     */
    private function getShipmentOptions(array $options): array
    {
        $keys            = array_keys((new ShipmentOptions())->getAttributes('snake'));
        $shipmentOptions = Arr::only($options, $keys);

        $shipmentOptions['insurance'] = $options['insurance']['amount'] ?? null;

        return $shipmentOptions;
    }
}
