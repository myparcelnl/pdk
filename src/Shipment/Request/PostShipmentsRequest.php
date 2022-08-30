<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Request;

use MyParcelNL\Pdk\Base\Request\AbstractRequest;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Concern\HasEncodesShipment;
use MyParcelNL\Pdk\Shipment\Model\Shipment;

class PostShipmentsRequest extends AbstractRequest
{
    use HasEncodesShipment;

    /**
     * @var \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection
     */
    private $collection;

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection $collection
     */
    public function __construct(ShipmentCollection $collection)
    {
        $this->collection = $collection;
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
                        return [$this->encodeShipmentWithSecondaryShipments($groupedShipments)];
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
        return '/shipments';
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

        /**
         * Turn grouped shipments into regular collection to avoid all shipment properties being added.
         */
        return (new Collection($groupedShipments->all()))
            ->map(function (Shipment $shipment) {
                return ['reference_identifier' => $shipment->referenceIdentifier];
            })
            ->toArray();
    }

    /**
     * @param  \MyParcelNL\Pdk\Base\Support\Collection $groupedShipments
     *
     * @return array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    private function encodeShipmentWithSecondaryShipments(Collection $groupedShipments): array
    {
        $shipment                        = $this->encodeShipment($groupedShipments->first());
        $shipment['secondary_shipments'] = $this->encodeSecondaryShipments($groupedShipments);

        return $shipment;
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
