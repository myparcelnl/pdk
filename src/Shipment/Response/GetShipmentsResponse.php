<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Response;

use MyParcelNL\Pdk\Api\Response\ApiResponseWithBody;
use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;

class GetShipmentsResponse extends ApiResponseWithBody
{
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

    /**
     * @param  array $data
     *
     * @return \MyParcelNL\Pdk\Shipment\Model\Shipment
     * @throws \Exception
     */
    private function decodeShipment(array $data): Shipment
    {
        $isReturn = in_array((int) $data['shipment_type'], Shipment::RETURN_SHIPMENT_TYPES, true);

        $options            = $data['options'] ?? [];
        $physicalProperties = $data['physical_properties'] ?? [];

        return new Shipment([
            'id'                       => $data['id'],
            'shopId'                   => $data['shop_id'],
            'barcode'                  => $data['barcode'],
            'carrier'                  => [
                'subscriptionId' => $data['contract_id'],
                'id'             => $data['carrier_id'],
            ],
            'collectionContact'        => $data['collection_contact'],
            'customsDeclaration'       => $this->filter($data['customs_declaration']),
            'delayed'                  => $data['delayed'],
            'delivered'                => $data['delivered'],
            'deliveryOptions'          => [
                'deliveryType'    => $options['delivery_type'],
                'packageType'     => $options['package_type'],
                'shipmentOptions' => $this->getShipmentOptions($options),
                'pickupLocation'  => $this->filter($data['pickup']),
            ],
            'dropOffPoint'             => $this->filter($data['drop_off_point']),
            'externalIdentifier'       => $data['external_identifier'],
            'hidden'                   => $data['hidden'],
            'isReturn'                 => $isReturn,
            'linkConsumerPortal'       => $data['link_consumer_portal'],
            'multiCollo'               => $data['multi_collo_main_shipment_id'] && $data['secondary_shipments'],
            'multiColloMainShipmentId' => $data['multi_collo_main_shipment_id'],
            'partnerTrackTraces'       => $data['partner_tracktraces'],
            'physicalProperties'       => $physicalProperties,
            'price'                    => $data['price'],
            'recipient'                => $this->filter($data['recipient']),
            'referenceIdentifier'      => $data['reference_identifier'],
            'sender'                   => $this->filter($data['sender']),
            'shipmentType'             => $data['shipment_type'],
            'status'                   => $data['status'],

            'created'    => $data['created'],
            'createdBy'  => $data['created_by'],
            'modified'   => $data['modified'],
            'modifiedBy' => $data['modified_by'],
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
        $keys            = array_keys((new ShipmentOptions())->getAttributes(Arrayable::CASE_SNAKE));
        $shipmentOptions = Arr::only($options, $keys);

        $shipmentOptions['insurance'] = $options['insurance']['amount'] ?? null;

        return $shipmentOptions;
    }
}
