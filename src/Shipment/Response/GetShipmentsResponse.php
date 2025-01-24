<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Response;

use MyParcelNL\Pdk\Api\Concern\DecodesAddressFields;
use MyParcelNL\Pdk\Api\Response\ApiResponseWithBody;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Base\Support\Str;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;

class GetShipmentsResponse extends ApiResponseWithBody
{
    use DecodesAddressFields;

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
                'id'         => $data['carrier_id'],
                'contractId' => $data['contract_id'],
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
            'recipient'                => $this->decodeAddress($data['recipient']),
            'referenceIdentifier'      => $data['reference_identifier'],
            'sender'                   => $this->decodeAddress($data['sender']),
            'shipmentType'             => $data['shipment_type'],
            'status'                   => $data['status'],

            'created'    => $data['created'],
            'createdBy'  => $data['created_by'],
            'modified'   => $data['modified'],
            'modifiedBy' => $data['modified_by'],
        ]);
    }

    /**
     * @param  array $options
     *
     * @return array
     */
    private function getShipmentOptions(array $options): array
    {
        $keys            = array_keys((new ShipmentOptions())->getAttributes(Str::CASE_SNAKE));
        $shipmentOptions = Arr::only($options, $keys);

        $shipmentOptions['insurance'] = $options['insurance']['amount'] ?? null;

        return $shipmentOptions;
    }
}
