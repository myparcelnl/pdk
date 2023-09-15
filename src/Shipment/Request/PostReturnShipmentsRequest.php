<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Request;

use MyParcelNL\Pdk\Api\Request\Request;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Model\Shipment;

class PostReturnShipmentsRequest extends Request
{
    public function __construct(private readonly ShipmentCollection $collection, array $parameters = [])
    {
        parent::__construct(['parameters' => $parameters]);
    }

    public function getBody(): string
    {
        return json_encode([
            'data' => [
                'return_shipments' => $this->encodeReturnShipments(),
            ],
        ]);
    }

    public function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/vnd.return_shipment+json;charset=utf-8',
        ];
    }

    public function getMethod(): string
    {
        return 'POST';
    }

    public function getPath(): string
    {
        return '/shipments';
    }

    /**
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    private function encodeReturnOptions(Shipment $shipment): array
    {
        $shipmentOptions = $shipment->deliveryOptions->shipmentOptions;
        $options         = array_map(static fn($item) => is_bool($item) ? (int) $item : $item,
            $shipmentOptions->toSnakeCaseArray());

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

    private function encodeReturnShipments(): array
    {
        return $this->collection->map(fn(Shipment $shipment) => [
            'parent'               => $shipment->id,
            'reference_identifier' => $shipment->referenceIdentifier,
            'carrier'              => $shipment->carrier->id,
            'email'                => $shipment->recipient->email,
            'name'                 => $shipment->recipient->person,
            'options'              => $this->encodeReturnOptions($shipment),
        ])
            ->toArray();
    }
}
