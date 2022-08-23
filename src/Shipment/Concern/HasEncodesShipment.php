<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Concern;

use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use MyParcelNL\Pdk\Shipment\Request\PostShipmentsRequest;

trait HasEncodesShipment
{
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

        return array_filter($shipment->dropOffPoint->toSnakeCaseArray()) + PostShipmentsRequest::DEFAULT_DROP_OFF_POINT;
    }

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Model\Shipment $shipment
     *
     * @return null|array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    private function encodeOptions(Shipment $shipment): ?array
    {
        $deliveryOptions = $shipment->deliveryOptions;
        $shipmentOptions = $shipment->deliveryOptions->shipmentOptions;

        if ($shipmentOptions) {
            $options = array_map(static function ($item) {
                return is_bool($item) ? (int) $item : $item;
            }, $shipmentOptions->toSnakeCaseArray());
        }

        return array_filter(
            [
                'package_type'  => $this->getPackageTypeId($shipment),
                'delivery_type' => $this->getDeliveryTypeId($shipment),
                'delivery_date' => $deliveryOptions->date ? $deliveryOptions->date->format('Y-m-d H:i:s') : null,
                'insurance'     => $shipmentOptions->insurance
                    ? [
                        'amount'   => $shipmentOptions->insurance * 100,
                        'currency' => 'EUR',
                    ] : null,
            ] + ($options ?? [])
        );
    }

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Model\Shipment $shipment
     *
     * @return null|int
     */
    private function getDeliveryTypeId(Shipment $shipment): ?int
    {
        return $shipment->deliveryOptions && $shipment->deliveryOptions->deliveryType
            ? DeliveryOptions::DELIVERY_TYPES_NAMES_IDS_MAP[$shipment->deliveryOptions->deliveryType]
            : null;
    }

    /**
     * @param  string $deliveryType
     *
     * @return null|string
     */
    private function getDeliveryTypeName(string $deliveryType): ?string
    {
        return array_flip(DeliveryOptions::DELIVERY_TYPES_NAMES_IDS_MAP)[$deliveryType] ?? null;
    }

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Model\Shipment $shipment
     *
     * @return null|int
     */
    private function getPackageTypeId(Shipment $shipment): ?int
    {
        return $shipment->deliveryOptions && $shipment->deliveryOptions->packageType
            ? DeliveryOptions::PACKAGE_TYPES_NAMES_IDS_MAP[$shipment->deliveryOptions->packageType]
            : null;
    }

    /**
     * @param  string $packageType
     *
     * @return null|string
     */
    private function getPackageTypeName(string $packageType): ?string
    {
        return array_flip(DeliveryOptions::PACKAGE_TYPES_NAMES_IDS_MAP)[$packageType] ?? null;
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
