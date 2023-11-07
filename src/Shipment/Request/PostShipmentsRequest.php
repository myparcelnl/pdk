<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Request;

use MyParcelNL\Pdk\Api\Request\Request;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Concern\EncodesCustomsDeclaration;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use MyParcelNL\Pdk\Types\Contract\TriStateServiceInterface;
use MyParcelNL\Pdk\Types\Service\TriStateService;

class PostShipmentsRequest extends Request
{
    use EncodesCustomsDeclaration;

    /**
     * @var \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection
     */
    private $collection;

    /**
     * @var \MyParcelNL\Pdk\Types\Contract\TriStateServiceInterface
     */
    private $triStateService;

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection $collection
     */
    public function __construct(ShipmentCollection $collection)
    {
        parent::__construct();
        $this->collection = $collection;

        $this->triStateService = Pdk::get(TriStateServiceInterface::class);
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
     * @param  \MyParcelNL\Pdk\Shipment\Model\Shipment $shipment
     *
     * @return array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    protected function encodeShipment(Shipment $shipment): array
    {
        return Utils::filterNull([
            'carrier'              => $shipment->carrier->id,
            'customs_declaration'  => $this->encodeCustomsDeclaration($shipment),
            'drop_off_point'       => $this->getDropOffPoint($shipment),
            'general_settings'     => [
                'save_recipient_address' => (int) Settings::get('order.saveCustomerAddress'),
            ],
            'options'              => $this->getOptions($shipment),
            'physical_properties'  => ['weight' => $this->getWeight($shipment)],
            'pickup'               => $this->getPickupLocation($shipment),
            'recipient'            => $this->getRecipient($shipment),
            'reference_identifier' => $shipment->referenceIdentifier,
        ]);
    }

    /**
     * @param  \MyParcelNL\Pdk\Base\Support\Collection $groupedShipments
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
        $shipment                        = $this->encodeShipment($groupedShipments->first());
        $shipment['secondary_shipments'] = $this->encodeSecondaryShipments($groupedShipments);

        return $shipment;
    }

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Model\Shipment $shipment
     *
     * @return null|array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    private function getDropOffPoint(Shipment $shipment): ?array
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

        return Utils::filterNull($shipment->dropOffPoint->toSnakeCaseArray()) + $defaults;
    }

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Model\Shipment $shipment
     *
     * @return array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    private function getOptions(Shipment $shipment): array
    {
        $shipmentOptions = $shipment->deliveryOptions->shipmentOptions ?? null;

        if (! $shipmentOptions) {
            return [];
        }

        $options = array_map(function ($item) {
            return $this->triStateService->resolve($item);
        }, $shipmentOptions->toSnakeCaseArray());

        $hasInsurance = $shipmentOptions->insurance > TriStateService::DISABLED;

        return array_filter(
            [
                'package_type'  => $shipment->deliveryOptions->getPackageTypeId(),
                'delivery_type' => $shipment->deliveryOptions->getDeliveryTypeId(),
                'delivery_date' => $shipment->deliveryOptions->getDateAsString(),
                'insurance'     => $hasInsurance
                    ? [
                        'amount'   => $shipmentOptions->insurance,
                        'currency' => 'EUR',
                    ] : null,
            ] + $options
        );
    }

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Model\Shipment $shipment
     *
     * @return null|array
     */
    private function getPickupLocation(Shipment $shipment): ?array
    {
        if (! $shipment->deliveryOptions->isPickup()) {
            return null;
        }

        $address = $shipment->deliveryOptions->pickupLocation;

        return Utils::filterNull([
            'location_code'     => $address->locationCode,
            'location_name'     => $address->locationName,
            'retail_network_id' => $address->retailNetworkId,
            'cc'                => $address->cc,
            'street'            => $address->street,
            'number'            => $address->number,
            'number_suffix'     => $address->numberSuffix,
            'box_number'        => $address->boxNumber,
            'postal_code'       => $address->postalCode,
            'city'              => $address->city,
            'region'            => $address->region,
            'state'             => $address->state,
        ]);
    }

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Model\Shipment $shipment
     *
     * @return array
     */
    private function getRecipient(Shipment $shipment): array
    {
        $recipient = $shipment->recipient;
        $street    = trim(implode(' ', [$recipient->address1, $recipient->address2])) ?: null;

        return Utils::filterNull([
            'area'                   => $recipient->area,
            'cc'                     => $recipient->cc,
            'city'                   => $recipient->city,
            'company'                => $recipient->company,
            'email'                  => $recipient->email,
            'person'                 => $recipient->person,
            'phone'                  => $recipient->phone,
            'postal_code'            => $recipient->postalCode,
            'region'                 => $recipient->region,
            'state'                  => $recipient->state,
            'street'                 => $street,
            'street_additional_info' => $recipient->address2,
            'eori_number'            => $recipient->eoriNumber,
            'vat_number'             => $recipient->vatNumber,
        ]);
    }

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Model\Shipment $shipment
     *
     * @return int
     */
    private function getWeight(Shipment $shipment): int
    {
        return $shipment->customsDeclaration->weight ?? $shipment->physicalProperties->weight ?? 0;
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
