<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Request;

use MyParcelNL\Pdk\Api\Request\Request;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Concern\EncodesRequestShipment;
use MyParcelNL\Pdk\Shipment\Model\Shipment;

class PostShipmentsRequest extends Request
{
    use EncodesRequestShipment;

    /**
     * @var \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection
     */
    private $collection;

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection $collection
     */
    public function __construct(ShipmentCollection $collection)
    {
        parent::__construct();
        $this->collection = $collection;
    }

    /**
     * @return string
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
            ] + parent::getHeaders();
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
     * @param  Collection $groupedShipments
     *
     * @return null|array
     */
    private function encodeSecondaryShipments(Collection $groupedShipments): ?array
    {
        $clonedCollection = new Collection($groupedShipments->all());

        $clonedCollection->shift();

        if ($clonedCollection->isEmpty()) {
            return null;
        }

        /**
         * Turn grouped shipments into regular collection to avoid all shipment properties being added.
         */
        return $clonedCollection
            ->map(function (Shipment $shipment) {
                return [
                    'reference_identifier' => $shipment->externalIdentifier,
                ];
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
        return array_merge(
            $this->encodeShipment($groupedShipments->first()),
            [
                'secondary_shipments' => $this->encodeSecondaryShipments($groupedShipments),
            ]
        );
    }

    /**
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    private function groupByMultiCollo(): Collection
    {
        return (new Collection($this->collection->all()))->groupBy(function ($shipment) {
            $shipment = Utils::cast(Shipment::class, $shipment);

            if ($shipment->multiCollo) {
                return $shipment->referenceIdentifier;
            }

            return uniqid('random_', true);
        });
    }
}
