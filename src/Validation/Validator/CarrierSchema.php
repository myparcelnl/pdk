<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Validation\Validator;

use BadMethodCallException;
use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Validation\Contract\DeliveryOptionsValidatorInterface;

class CarrierSchema implements DeliveryOptionsValidatorInterface
{
    /**
     * @var array
     */
    protected $cache = [];

    /**
     * @var \MyParcelNL\Pdk\Carrier\Model\Carrier|null
     */
    protected $carrier;

    public function canBeDigitalStamp(): bool
    {
        return $this->canHavePackageType(DeliveryOptions::PACKAGE_TYPE_DIGITAL_STAMP_NAME);
    }

    public function canBeLetter(): bool
    {
        return $this->canHavePackageType(DeliveryOptions::PACKAGE_TYPE_LETTER_NAME);
    }

    public function canBeMailbox(): bool
    {
        return $this->canHavePackageType(DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME);
    }

    public function canBePackage(): bool
    {
        return $this->canHavePackageType(DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME);
    }

    /**
     * @param  \MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface $definition
     *
     * @return bool
     */
    public function canHave(OrderOptionDefinitionInterface $definition): bool
    {
        return (bool) $this->getShipmentOption($definition->getShipmentOptionsKey());
    }

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
        return (bool) $this->getShipmentOption(ShipmentOptions::DIRECT_RETURN);
    }

    public function canHaveEveningDelivery(): bool
    {
        return $this->hasDeliveryType(DeliveryOptions::DELIVERY_TYPE_EVENING_NAME);
    }

    /**
     * @return bool
     */
    public function canHaveHideSender(): bool
    {
        return (bool) $this->getShipmentOption(ShipmentOptions::HIDE_SENDER);
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
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function getSchema(): array
    {
        $identifier = $this->getCarrier()->externalIdentifier;

        if (! isset($this->cache[$identifier])) {
            $this->cache[$identifier] = $this->createSchema();
        }

        return $this->cache[$identifier];
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
     * @param  string $packageType
     *
     * @return bool
     */
    protected function canHavePackageType(string $packageType): bool
    {
        return in_array($packageType, $this->getAllowedPackageTypes(), true);
    }

    /**
     * @return \MyParcelNL\Pdk\Carrier\Model\Carrier
     */
    protected function getCarrier(): Carrier
    {
        if (! $this->carrier) {
            throw new BadMethodCallException('Carrier not set');
        }

        return $this->carrier;
    }

    /**
     * @return array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    private function createSchema(): array
    {
        return $this->getCarrier()->capabilities->toArray();
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
