<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Request;

use MyParcelNL\Pdk\Api\Request\Request;
use MyParcelNL\Pdk\Base\Contract\CountryServiceInterface;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Model\Shipment;

class PostShipmentsRequest extends Request
{
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
        $customsDeclaration = $this->getCustomsDeclaration($shipment);
        $carrierId          = $shipment->carrier->carrier->id
            ?: Carrier::CARRIER_NAME_ID_MAP[$shipment->carrier->carrier->name];

        return [
            'carrier'              => $carrierId,
            'customs_declaration'  => $customsDeclaration,
            'drop_off_point'       => $this->getDropOffPoint($shipment),
            'general_settings'     => [
                'save_recipient_address' => (int) Settings::get('order.saveCustomerAddress'),
            ],
            'options'              => $this->getOptions($shipment),
            'physical_properties'  => ['weight' => $this->getWeight($shipment)],
            'pickup'               => $shipment->deliveryOptions->pickupLocation
                ? ['location_code' => $shipment->deliveryOptions->pickupLocation->locationCode]
                : null,
            'recipient'            => $this->getRecipient($shipment),
            'reference_identifier' => $shipment->referenceIdentifier,
            'status'               => $shipment->status,
        ];
    }

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection $groupedShipments
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
     * @return null|array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    private function getOptions(Shipment $shipment): ?array
    {
        $shipmentOptions = $shipment->deliveryOptions->shipmentOptions;
        $capabilities    = $shipment->carrier->capabilities;

        if (Carrier::CARRIER_DHL_FOR_YOU_NAME === $shipment->carrier->carrier->name
            && false === $capabilities->shipmentOptions['sameDayDelivery']) {
            $shipmentOptions->sameDayDelivery = '1';
        }

        $options = array_map(static function ($item) {
            return is_bool($item) ? (int) $item : $item;
        }, $shipmentOptions->toSnakeCaseArray());

        return array_filter(
            [
                'package_type'  => $shipment->deliveryOptions->getPackageTypeId(),
                'delivery_type' => $shipment->deliveryOptions->getDeliveryTypeId(),
                'delivery_date' => $shipment->deliveryOptions->getDateAsString(),
                'insurance'     => $shipmentOptions->insurance
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
     * @return array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    private function getRecipient(Shipment $shipment): array
    {
        $recipient = Utils::filterNull($shipment->recipient->toSnakeCaseArray());

        if ($recipient['full_street']) {
            $recipient['street'] = $recipient['full_street'];
            unset($recipient['full_street'], $recipient['number'], $recipient['number_suffix']);
        }

        return $recipient;
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
