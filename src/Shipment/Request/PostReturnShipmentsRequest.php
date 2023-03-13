<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Request;

use MyParcelNL\Pdk\Api\Request\Request;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Model\Shipment;

class PostReturnShipmentsRequest extends Request
{
    /**
     * @var \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection
     */
    private $collection;

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection $shipmentCollection
     * @param  array                                                  $parameters
     */
    public function __construct(ShipmentCollection $shipmentCollection, array $parameters = [])
    {
        $this->collection = $shipmentCollection;
        parent::__construct(['parameters' => $parameters]);
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return json_encode([
            'data' => [
                'return_shipments' => $this->encodeReturnShipments(),
            ],
        ]);
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/vnd.return_shipment+json;charset=utf-8',
        ];
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return 'POST';
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return '/shipments';
    }

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Model\Shipment $shipment
     *
     * @return array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    private function encodeReturnOptions(Shipment $shipment): array
    {
        $shipmentOptions = $shipment->deliveryOptions->shipmentOptions;
        $options         = array_map(static function ($item) {
            return is_bool($item) ? (int) $item : $item;
        }, $shipmentOptions->toSnakeCaseArray());

        return array_filter(
            [
                'package_type' => $shipment->deliveryOptions->getPackageTypeId(),
                'insurance'    => $shipmentOptions->insurance
                    ? [
                        'amount'   => $shipmentOptions->insurance * 100,
                        'currency' => 'EUR',
                    ] : null,
            ] + $options
        );
    }

    /**
     * @return array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    private function encodeReturnShipments(): array
    {
        return $this->collection->map(function (Shipment $shipment) {
            return [
                'parent'               => $shipment->id,
                'reference_identifier' => $shipment->referenceIdentifier,
                'carrier'              => $shipment->carrier->carrier->id,
                'email'                => $shipment->recipient->email,
                'name'                 => $shipment->recipient->person,
                'options'              => $this->encodeReturnOptions($shipment),
            ];
        })
            ->toArray();
    }
}
