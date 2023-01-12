<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Concern;

use MyParcelNL\Pdk\Base\Service\CountryService;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Model\Shipment;

trait HasEncodesShipment
{
    /**
     * @param  \MyParcelNL\Pdk\Shipment\Model\Shipment $shipment
     *
     * @return array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    protected function encodeShipment(Shipment $shipment): array
    {
        $customsDeclaration = $this->getCustomsDeclaration($shipment);

        return [
            'carrier'              => $shipment->carrier->id,
            'customs_declaration'  => $customsDeclaration,
            'drop_off_point'       => $this->getDropOffPoint($shipment),
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
     * @param  \MyParcelNL\Pdk\Shipment\Model\Shipment $shipment
     *
     * @return null|array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    private function getCustomsDeclaration(Shipment $shipment): ?array
    {
        /** @var \MyParcelNL\Pdk\Base\Service\CountryService $countryService */
        $countryService = Pdk::get(CountryService::class);
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
        $options         = array_map(static function ($item) {
            return is_bool($item) ? (int) $item : $item;
        }, $shipmentOptions->toSnakeCaseArray());

        return array_filter(
            [
                'package_type'  => $shipment->deliveryOptions->getPackageTypeId(),
                'delivery_type' => $shipment->deliveryOptions->getDeliveryTypeId(),
                'delivery_date' => $shipment->deliveryOptions->getDateAsString(),
                'insurance'     => $shipmentOptions->insurance
                    ? [
                        'amount'   => $shipmentOptions->insurance * 100,
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
}
