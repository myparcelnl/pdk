<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Concern;

use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Model\Shipment;

trait HasEncodesShipment
{
    /**
     * @param  \MyParcelNL\Pdk\Shipment\Model\Shipment $shipment
     *
     * @return array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    protected function encodeShipment(Shipment $shipment): array
    {
        $pickup = $this->verifyPickup($shipment);

        return [
                'carrier'              => $shipment->carrier->id,
                'customs_declaration'  => $shipment->customsDeclaration
                    ? array_filter($shipment->customsDeclaration->toSnakeCaseArray())
                    : null,
                'drop_off_point'       => $this->encodeDropOffPoint($shipment),
                'options'              => $this->encodeOptions($shipment),
                'physical_properties'  => $shipment->physicalProperties
                    ? ['weight' => $this->getWeight($shipment)]
                    : null,
                'recipient'            => array_filter($shipment->recipient->toSnakeCaseArray()),
                'reference_identifier' => $shipment->referenceIdentifier,
                'status'               => $shipment->status,
            ] + $pickup;
    }

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection $collection
     *
     * @return array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    protected function encodeShipmentCollection(ShipmentCollection $collection): array
    {
        $result = [];
        foreach ($collection as $shipment) {
            $result[] = $this->encodeShipment($shipment);
        }

        return $result;
    }

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Model\Shipment $shipment
     *
     * @return array|string[]
     */
    protected function verifyPickup(Shipment $shipment): array
    {
        if (empty($shipment->deliveryOptions->pickupLocation)) {
            return [''];
        }

        return [
            'pickup' => [
                'location_code' => $shipment->deliveryOptions->pickupLocation->locationCode,
            ],
        ];
    }

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Model\Shipment $shipment
     *
     * @return null|array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    private function encodeDropOffPoint(Shipment $shipment): ?array
    {
        if (! $shipment->dropOffPoint) {
            return null;
        }

        /**
         * API currently does not support only sending location_code, however, the following properties are not used or
         * validated beyond "must be a string".
         */
        $defaults = [
            'postal_code'   => '',
            'location_name' => '',
            'city'          => '',
            'street'        => '',
            'number'        => '',
        ];

        return array_filter($shipment->dropOffPoint->toSnakeCaseArray()) + $defaults;
    }

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Model\Shipment $shipment
     *
     * @return null|array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    private function encodeOptions(Shipment $shipment): ?array
    {
        $shipmentOptions = $shipment->deliveryOptions->shipmentOptions;
        $options         = array_map(static function ($item) {
            return is_bool($item) ? (int) $item : $item;
        }, $shipmentOptions->toSnakeCaseArray());

        return array_filter(
            [
                'package_type'  => $shipment->deliveryOptions->getPackageTypeId(),
                'delivery_type' => $shipment->deliveryOptions->getDeliveryTypeId(),
                'delivery_date' => $shipment->deliveryOptions->getDateAsString(),
                'insurance'     => $shipmentOptions->insurance
                    ? [
                        'amount'   => $shipmentOptions->insurance * 100,
                        'currency' => 'EUR',
                    ] : null,
            ] + $options
        );
    }

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Model\Shipment $shipment
     *
     * @return null|int
     */
    private function getWeight(Shipment $shipment): ?int
    {
        return $shipment->customsDeclaration->weight ?? $shipment->physicalProperties->weight;
    }
}
