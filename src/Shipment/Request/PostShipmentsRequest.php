<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Request;

use MyParcelNL\Pdk\Base\Request\AbstractRequest;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\Shipment;

class PostShipmentsRequest extends AbstractRequest
{
    /**
     * API currently does not support only sending location_code, however, the following properties are not used or
     * validated beyond "must be a string".
     */
    private const DEFAULT_DROP_OFF_POINT = [
        'postal_code'   => '',
        'location_name' => '',
        'city'          => '',
        'street'        => '',
        'number'        => '',
    ];

    /**
     * @var string
     */
    protected $path = '/shipments';

    /**
     * @var \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection
     */
    private $collection;

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection $collection
     */
    public function __construct(ShipmentCollection $collection)
    {
        $this->collection = new Collection($collection->all());
    }

    /**
     * @return null|string
     */
    public function getBody(): string
    {
        return json_encode([
            'data' => [
                'shipments' => $this->groupByMultiCollo()
                    ->flatMap(function (Collection $groupedShipments) {
                        return [$this->encodeShipment($groupedShipments)];
                    })
                    ->toArrayWithoutNull(),
            ],
        ]);
    }

    /**
     * @return string[]
     */
    public function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/vnd.shipment+json;charset=utf-8;version=1.1',
        ];
    }

    /**
     * @return string
     */
    public function getHttpMethod(): string
    {
        return 'POST';
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
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

        return array_filter($shipment->dropOffPoint->toSnakeCaseArray()) + self::DEFAULT_DROP_OFF_POINT;
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
     * @param  \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection $groupedShipments
     *
     * @return null|array
     */
    private function encodeSecondaryShipments(Collection $groupedShipments): ?array
    {
        $groupedShipments->shift();

        if ($groupedShipments->isEmpty()) {
            return null;
        }

        return $groupedShipments
            ->map(function (Shipment $shipment) {
                return ['reference_identifier' => $shipment->referenceIdentifier];
            })
            ->toArray();
    }

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection $groupedShipments
     *
     * @return array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    private function encodeShipment(Collection $groupedShipments): array
    {
        $mainShipment = $groupedShipments->first();

        return [
            'carrier'              => $mainShipment->carrier->id,
            'reference_identifier' => $mainShipment->referenceIdentifier,
            'status'               => $mainShipment->status,
            'options'              => $this->encodeOptions($mainShipment),
            'physical_properties'  => $mainShipment->physicalProperties
                ? ['weight' => $this->getWeight($mainShipment)]
                : null,
            'pickup'               => $mainShipment->deliveryOptions->pickupLocation
                ? ['location_code' => $mainShipment->deliveryOptions->pickupLocation->locationCode]
                : null,
            'drop_off_point'       => $this->encodeDropOffPoint($mainShipment),
            'customs_declaration'  => $mainShipment->customsDeclaration
                ? array_filter($mainShipment->customsDeclaration->toSnakeCaseArray())
                : null,
            'recipient'            => array_filter($mainShipment->recipient->toSnakeCaseArray()),
            'secondary_shipments'  => $this->encodeSecondaryShipments($groupedShipments),
        ];
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
     * @param  \MyParcelNL\Pdk\Shipment\Model\Shipment $mainShipment
     *
     * @return null|int
     */
    private function getWeight(Shipment $mainShipment): ?int
    {
        return $mainShipment->customsDeclaration->weight ?? $mainShipment->physicalProperties->weight;
    }

    /**
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    private function groupByMultiCollo(): Collection
    {
        return $this->collection->groupBy(function (Shipment $shipment) {
            if ($shipment->multiCollo) {
                return $shipment->referenceIdentifier;
            }

            return uniqid('random_', true);
        });
    }
}
