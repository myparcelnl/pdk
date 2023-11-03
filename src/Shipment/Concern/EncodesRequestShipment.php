<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Concern;

use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Base\Contract\CountryServiceInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Shipment\Model\CustomsDeclaration;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use MyParcelNL\Pdk\Types\Contract\TriStateServiceInterface;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use RuntimeException;

trait EncodesRequestShipment
{
    use EncodesAddresses;

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Model\Shipment $shipment
     *
     * @return array
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
            'recipient'            => $this->encodeAddress($shipment->recipient),
            'reference_identifier' => $shipment->referenceIdentifier,
            'status'               => $shipment->status,
        ];
    }

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Model\Shipment $shipment
     *
     * @return \MyParcelNL\Pdk\Shipment\Model\CustomsDeclaration
     */
    private function generateCustomsDeclaration(Shipment $shipment): CustomsDeclaration
    {
        /** @var PdkOrderRepositoryInterface $orderRepository */
        $orderRepository = Pdk::get(PdkOrderRepositoryInterface::class);

        if (! $shipment->orderId) {
            throw new RuntimeException('Cannot generate customs declaration without order ID');
        }

        return CustomsDeclaration::fromPdkOrder($orderRepository->get($shipment->orderId));
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

        $cc = $shipment->recipient->cc;

        if (! $cc || ! $countryService->isRow($cc)) {
            return null;
        }

        $customsDeclaration = $shipment->customsDeclaration ?? $this->generateCustomsDeclaration($shipment);

        return $customsDeclaration->toArray(Arrayable::ENCODED);
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

        return $this->encodeAddress($shipment->dropOffPoint);
    }

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Model\Shipment $shipment
     *
     * @return array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    private function getOptions(Shipment $shipment): array
    {
        /** @var TriStateServiceInterface $triStateService */
        $triStateService = Pdk::get(TriStateServiceInterface::class);

        $shipmentOptions = $shipment->deliveryOptions->shipmentOptions;

        $options = array_map(static function ($item) use ($triStateService) {
            return $triStateService->resolve($item);
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
        return $shipment->deliveryOptions->isPickup()
            ? $this->encodeAddress($shipment->deliveryOptions->pickupLocation)
            : null;
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
