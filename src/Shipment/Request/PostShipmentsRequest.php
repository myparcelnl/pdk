<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Request;

use MyParcelNL\Pdk\Api\Request\Request;
use MyParcelNL\Pdk\Base\Contract\CountryServiceInterface;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use MyParcelNL\Pdk\Types\Contract\TriStateServiceInterface;
use MyParcelNL\Pdk\Types\Service\TriStateService;

class PostShipmentsRequest extends Request
{
    /**
     * @var \MyParcelNL\Pdk\Types\Contract\TriStateServiceInterface
     */
    private $triStateService;

    public function __construct(private readonly ShipmentCollection $collection)
    {
        parent::__construct();

        $this->triStateService = Pdk::get(TriStateServiceInterface::class);
    }

    /**
     * @return null|string
     */
    public function getBody(): string
    {
        return json_encode([
            'data' => [
                'shipments' => $this->groupByMultiCollo()
                    ->flatMap(
                        fn(Collection $groupedShipments) => [
                            $this->encodeShipmentWithSecondaryShipments($groupedShipments),
                        ]
                    )
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
    protected function encodeShipment(Shipment $shipment): array
    {
        return [
            'carrier'              => $shipment->carrier->id,
            'customs_declaration'  => $this->getCustomsDeclaration($shipment),
            'drop_off_point'       => $this->getDropOffPoint($shipment),
            'general_settings'     => [
                'save_recipient_address' => (int) Settings::get('order.saveCustomerAddress'),
            ],
            'options'              => $this->getOptions($shipment),
            'physical_properties'  => ['weight' => $this->getWeight($shipment)],
            'pickup'               => $this->getPickupLocation($shipment),
            'recipient'            => $this->getRecipient($shipment),
            'reference_identifier' => $shipment->referenceIdentifier,
            'status'               => $shipment->status,
        ];
    }

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection $groupedShipments
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
            ->map(fn(Shipment $shipment) => [
                'reference_identifier' => $shipment->externalIdentifier,
            ])
            ->toArray();
    }

    /**
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    private function encodeShipmentWithSecondaryShipments(Collection $groupedShipments): array
    {
        $shipment                        = $this->encodeShipment($groupedShipments->first());
        $shipment['secondary_shipments'] = $this->encodeSecondaryShipments($groupedShipments);

        return $shipment;
    }

    /**
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    private function getCustomsDeclaration(Shipment $shipment): ?array
    {
        /** @var \MyParcelNL\Pdk\Base\Contract\CountryServiceInterface $countryService */
        $countryService = Pdk::get(CountryServiceInterface::class);
        $cc             = $shipment->recipient->cc;

        if (! $cc || ! $countryService->isRow($cc)) {
            return null;
        }

        return $shipment->customsDeclaration
            ? Utils::filterNull($shipment->customsDeclaration->toSnakeCaseArray())
            : null;
    }

    /**
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
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    private function getOptions(Shipment $shipment): array
    {
        $shipmentOptions = $shipment->deliveryOptions->shipmentOptions;

        $options = array_map(fn($item) => $this->triStateService->resolve($item), $shipmentOptions->toSnakeCaseArray());

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

    private function getRecipient(Shipment $shipment): array
    {
        $recipient = $shipment->recipient;

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
            'street'                 => $recipient->address1,
            'street_additional_info' => $recipient->address2,
            'eori_number'            => $recipient->eoriNumber,
            'vat_number'             => $recipient->vatNumber,
        ]);
    }

    private function getWeight(Shipment $shipment): int
    {
        return $shipment->customsDeclaration->weight ?? $shipment->physicalProperties->weight ?? 0;
    }

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
