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
use MyParcelNL\Pdk\Shipment\Concern\EncodesRecipient;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use MyParcelNL\Pdk\Types\Contract\TriStateServiceInterface;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use MyParcelNL\Pdk\Validation\Validator\CarrierSchema;

class PostShipmentsRequest extends Request
{
    use EncodesCustomsDeclaration;
    use EncodesRecipient;

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
                'shipments' => (new Collection($this->collection))
                    ->map(function (Shipment $shipment) {
                        return $this->encodeShipment($shipment);
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
     */
    protected function encodeShipment(Shipment $shipment): array
    {
        return Utils::filterNull([
            'carrier'              => $shipment->carrier->id,
            'contract_id'          => $shipment->carrier->contractId
                ? (int) $shipment->carrier->contractId
                : null,
            'customs_declaration'  => $this->encodeCustomsDeclaration($shipment),
            'drop_off_point'       => $this->getDropOffPoint($shipment),
            'general_settings'     => [
                'save_recipient_address' => (int) Settings::get('order.saveCustomerAddress'),
            ],
            'options'              => $this->getOptions($shipment),
            'physical_properties'  => ['weight' => $this->getWeight($shipment)],
            'pickup'               => $this->getPickupLocation($shipment),
            'recipient'            => $this->encodeRecipient($shipment->recipient),
            'reference_identifier' => $shipment->referenceIdentifier,
            'secondary_shipments'  => $this->encodeSecondaryShipments($shipment),
        ]);
    }

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Model\Shipment $shipment
     *
     * @return null|array
     */
    private function encodeSecondaryShipments(Shipment $shipment): ?array
    {
        $schema = Pdk::get(CarrierSchema::class);

        $schema->setCarrier($shipment->deliveryOptions->carrier);

        $secondaryShipmentsAmount = $schema->canHaveMultiCollo() ? $shipment->deliveryOptions->labelAmount - 1 : 0;
        $secondaryShipments       = [];

        for ($i = 0; $i < $secondaryShipmentsAmount; $i++) {
            $secondaryShipments[] = ['reference_identifier' => $shipment->referenceIdentifier];
        }

        return $secondaryShipments ?: null;
    }

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Model\Shipment $shipment
     *
     * @return null|array
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
     * @return int
     */
    private function getWeight(Shipment $shipment): int
    {
        return $shipment->customsDeclaration->weight ?? $shipment->physicalProperties->weight ?? 0;
    }
}
