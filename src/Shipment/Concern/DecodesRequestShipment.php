<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Concern;

use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Shipment\Model\Shipment;

trait DecodesRequestShipment
{
    use DecodesDeliveryOptions;

    /**
     * @param  array $shipment
     *
     * @return array
     * @throws \Exception
     */
    private function decodeShipment(array $shipment): array
    {
        return [
            'id'                       => Arr::get($shipment, 'id'),
            'uuid'                     => Arr::get($shipment, 'uuid'),
            'shopId'                   => Arr::get($shipment, 'shop_id'),
            'referenceIdentifier'      => Arr::get($shipment, 'reference_identifier'),
            'barcode'                  => Arr::get($shipment, 'barcode'),
            'carrier'                  => $this->decodeCarrier($shipment),
            'collectionContact'        => Arr::get($shipment, 'collection_contact'),
            'customsDeclaration'       => Arr::get($shipment, 'customs_declaration'),
            'deliveryOptions'          => $this->decodeDeliveryOptions($shipment),
            'dropOffPoint'             => $this->decodeRetailLocation(Arr::get($shipment, 'drop_off_point') ?? []),
            'externalIdentifier'       => Arr::get($shipment, 'external_identifier'),
            'hidden'                   => Arr::get($shipment, 'hidden'),
            'isReturn'                 => $this->isReturn($shipment),
            'linkConsumerPortal'       => Arr::get($shipment, 'link_consumer_portal'),
            'multiCollo'               => $this->isMultiCollo($shipment),
            'multiColloMainShipmentId' => Arr::get($shipment, 'multi_collo_main_shipment_id'),
            'partnerTrackTraces'       => Arr::get($shipment, 'partner_tracktraces'),
            'physicalProperties'       => Arr::get($shipment, 'physical_properties') ?? [],
            'price'                    => Arr::get($shipment, 'price'),
            'recipient'                => $this->decodeAddress(Arr::get($shipment, 'recipient', [])),
            'sender'                   => $this->decodeAddress(Arr::get($shipment, 'sender', [])),
            'shipmentType'             => Arr::get($shipment, 'shipment_type'),
            'status'                   => Arr::get($shipment, 'status'),
            'delayed'                  => Arr::get($shipment, 'delayed'),
            'delivered'                => Arr::get($shipment, 'delivered'),

            'created'    => Arr::get($shipment, 'created'),
            'createdBy'  => Arr::get($shipment, 'created_by'),
            'modified'   => Arr::get($shipment, 'modified'),
            'modifiedBy' => Arr::get($shipment, 'modified_by'),
        ];
    }

    /**
     * @param  array $data
     *
     * @return bool
     */
    private function isMultiCollo(array $data): bool
    {
        $secondaryShipments = Arr::get($data, 'secondary_shipments');

        return Arr::get($data, 'multi_collo_main_shipment_id') && count($secondaryShipments);
    }

    /**
     * @param  array $shipment
     *
     * @return bool
     */
    private function isReturn(array $shipment): bool
    {
        return in_array((int) Arr::get($shipment, 'shipment_type'), Shipment::RETURN_SHIPMENT_TYPES, true);
    }
}
