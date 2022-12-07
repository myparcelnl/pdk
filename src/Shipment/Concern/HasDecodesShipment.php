<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Concern;

use MyParcelNL\Pdk\Base\Support\Arrayable;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Sdk\src\Support\Arr;

trait HasDecodesShipment
{
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
            'carrier'                  => [
                'subscriptionId' => $data['contract_id'],
                'id'             => $data['carrier_id'],
            ],
            'status'                   => $data['status'],
            'barcode'                  => $data['barcode'],
            'isReturn'                 => $isReturn,
            'recipient'                => $this->filter($data['recipient']),
            'sender'                   => $this->filter($data['sender']),
            'deliveryOptions'          => [
                'deliveryType'    => $options['delivery_type'],
                'packageType'     => $options['package_type'],
                'shipmentOptions' => $this->getShipmentOptions($options),
                'pickupLocation'  => $this->filter($data['pickup']),
            ],
            'dropOffPoint'             => $this->filter($data['drop_off_point']),
            'customsDeclaration'       => $this->filter($data['customs_declaration']),
            'physicalProperties'       => $physicalProperties
                ? Arr::only($physicalProperties, ['height', 'length', 'weight', 'width'])
                : null,
            'collectionContact'        => $data['collection_contact'],
            'delayed'                  => $data['delayed'],
            'delivered'                => $data['delivered'],
            'externalIdentifier'       => $data['external_identifier'],
            'linkConsumerPortal'       => $data['link_consumer_portal'],
            'multiColloMainShipmentId' => $data['multi_collo_main_shipment_id'],
            'partnerTrackTraces'       => $data['partner_tracktraces'],
            'referenceIdentifier'      => $data['reference_identifier'],
            'updated'                  => $data['updated'],
            'created'                  => $data['created'],
            'createdBy'                => $data['created_by'],
            'modified'                 => $data['modified'],
            'modifiedBy'               => $data['modified_by'],
            'multiCollo'               => $data['multi_collo_main_shipment_id'] && $data['secondary_shipments'],
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
