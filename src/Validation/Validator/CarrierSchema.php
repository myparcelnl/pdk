<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Validation\Validator;

use Exception;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Validation\Contract\DeliveryOptionsValidatorInterface;

class CarrierSchema implements DeliveryOptionsValidatorInterface
{
    /**
     * @var \MyParcelNL\Pdk\Carrier\Model\Carrier
     */
    protected $carrier;

    public function canHaveAgeCheck(): bool
    {
        return (bool) $this->getShipmentOption(ShipmentOptions::AGE_CHECK);
    }

    public function canHaveDate(): bool
    {
        return true;
    }

    public function canHaveDirectReturn(): bool
    {
        return (bool) $this->getShipmentOption(ShipmentOptions::RETURN);
    }

    public function canHaveEveningDelivery(): bool
    {
        return $this->hasDeliveryType(DeliveryOptions::DELIVERY_TYPE_EVENING_NAME);
    }

    /**
     * We can safely ignore the amount here as it's not used in the capabilities.
     *
     * @param  null|int $amount
     *
     * @return bool
     */
    public function canHaveInsurance(?int $amount = 0): bool
    {
        return (bool) $this->getShipmentOption(ShipmentOptions::INSURANCE);
    }

    public function canHaveLargeFormat(): bool
    {
        return (bool) $this->getShipmentOption(ShipmentOptions::LARGE_FORMAT);
    }

    public function canHaveMorningDelivery(): bool
    {
        return $this->hasDeliveryType(DeliveryOptions::DELIVERY_TYPE_MORNING_NAME);
    }

    public function canHaveMultiCollo(): bool
    {
        return (bool) $this->getFeature('multiCollo');
    }

    public function canHaveOnlyRecipient(): bool
    {
        return (bool) $this->getShipmentOption(ShipmentOptions::ONLY_RECIPIENT);
    }

    public function canHavePickup(): bool
    {
        return $this->hasDeliveryType(DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME);
    }

    public function canHaveSameDayDelivery(): bool
    {
        return (bool) $this->getShipmentOption(ShipmentOptions::SAME_DAY_DELIVERY);
    }

    public function canHaveSignature(): bool
    {
        return (bool) $this->getShipmentOption(ShipmentOptions::SIGNATURE);
    }

    public function canHaveWeight(?int $weight): bool
    {
        return true;
    }

    public function getAllowedInsuranceAmounts(): array
    {
        return $this->getShipmentOption(ShipmentOptions::INSURANCE) ?: [];
    }

    public function getAllowedPackageTypes(): array
    {
        return $this->getFromSchema('packageTypes') ?: [];
    }

    /**
     * @return array
     */
    public function getSchema(): array
    {
        try {
            $capabilities = $this->carrier->capabilities->toArray();
        } catch (Exception $e) {
            Logger::warning('Could not get capabilities from carrier options', [
                'exception' => $e,
            ]);

            $capabilities = [];
        }

        return $capabilities;
    }

    /**
     * @param  \MyParcelNL\Pdk\Carrier\Model\Carrier $carrier
     *
     * @return self
     */
    public function setCarrier(Carrier $carrier): self
    {
        $this->carrier = $carrier;
        return $this;
    }

    /**
     * @param  string $feature
     *
     * @return mixed
     */
    private function getFeature(string $feature)
    {
        return $this->getFromSchema(sprintf('features.%s', $feature));
    }

    /**
     * @param  string $key
     *
     * @return mixed
     */
    private function getFromSchema(string $key)
    {
        return Arr::get($this->getSchema(), $key);
    }

    /**
     * @param  string $shipmentOption
     *
     * @return mixed
     */
    private function getShipmentOption(string $shipmentOption)
    {
        return $this->getFromSchema(sprintf('shipmentOptions.%s', $shipmentOption));
    }

    /**
     * @param  string $deliveryType
     *
     * @return bool
     */
    private function hasDeliveryType(string $deliveryType): bool
    {
        return in_array($deliveryType, $this->getFromSchema('deliveryTypes') ?? [], true);
    }
}
