<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Validation\Validator;

use BadMethodCallException;
use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\App\Options\Definition\AgeCheckDefinition;
use MyParcelNL\Pdk\App\Options\Definition\CollectDefinition;
use MyParcelNL\Pdk\App\Options\Definition\DirectReturnDefinition;
use MyParcelNL\Pdk\App\Options\Definition\HideSenderDefinition;
use MyParcelNL\Pdk\App\Options\Definition\InsuranceDefinition;
use MyParcelNL\Pdk\App\Options\Definition\LargeFormatDefinition;
use MyParcelNL\Pdk\App\Options\Definition\OnlyRecipientDefinition;
use MyParcelNL\Pdk\App\Options\Definition\ReceiptCodeDefinition;
use MyParcelNL\Pdk\App\Options\Definition\SameDayDeliveryDefinition;
use MyParcelNL\Pdk\App\Options\Definition\SignatureDefinition;
use MyParcelNL\Pdk\App\Options\Definition\TrackedDefinition;
use MyParcelNL\Pdk\App\Options\Definition\FoodDefinition;
use MyParcelNL\Pdk\App\Options\Definition\FrozenFoodDefinition;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Validation\Contract\DeliveryOptionsValidatorInterface;

class CarrierSchema implements DeliveryOptionsValidatorInterface
{
    /**
     * Used for features to indicate that the feature is only available for custom contracts.
     */
    public const FEATURE_CUSTOM_CONTRACT_ONLY = 'featureCustomContractOnly';

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

    public function canBePackageSmall(): bool
    {
        return $this->canHavePackageType(DeliveryOptions::PACKAGE_TYPE_PACKAGE_SMALL_NAME);
    }

    public function canHaveAgeCheck(): bool
    {
        return $this->canHave(AgeCheckDefinition::class);
    }

    public function canHaveCarrierSmallPackageContract(): bool
    {
        return $this->canHaveFeature('carrierSmallPackageContract');
    }

    public function canHaveCollect(): bool
    {
        return $this->canHave(CollectDefinition::class);
    }

    public function canHaveDirectReturn(): bool
    {
        return $this->canHave(DirectReturnDefinition::class);
    }

    public function canHaveEveningDelivery(): bool
    {
        return $this->hasDeliveryType(DeliveryOptions::DELIVERY_TYPE_EVENING_NAME);
    }

    public function canHaveExpressDelivery(): bool
    {
        return $this->hasDeliveryType(DeliveryOptions::DELIVERY_TYPE_EXPRESS_NAME);
    }

    /**
     * @return bool
     */
    public function canHaveHideSender(): bool
    {
        return $this->canHave(HideSenderDefinition::class);
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
        return $this->canHave(InsuranceDefinition::class);
    }

    public function canHaveLargeFormat(): bool
    {
        return $this->canHave(LargeFormatDefinition::class);
    }

    public function canHaveMorningDelivery(): bool
    {
        return $this->hasDeliveryType(DeliveryOptions::DELIVERY_TYPE_MORNING_NAME);
    }

    public function canHaveMultiCollo(): bool
    {
        return $this->canHaveFeature('multiCollo');
    }

    public function canHaveOnlyRecipient(): bool
    {
        return $this->canHave(OnlyRecipientDefinition::class);
    }

    public function canHavePickup(): bool
    {
        return $this->hasDeliveryType(DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME);
    }

    public function canHaveReceiptCode(): bool
    {
        return $this->canHave(ReceiptCodeDefinition::class);
    }

    public function canHaveSameDayDelivery(): bool
    {
        return $this->canHave(SameDayDeliveryDefinition::class);
    }

    public function canHaveSignature(): bool
    {
        return $this->canHave(SignatureDefinition::class);
    }

    public function canHaveStandardDelivery(): bool
    {
        return $this->hasDeliveryType(DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME);
    }

    public function canHaveTracked(): bool
    {
        return $this->canHave(TrackedDefinition::class);
    }

    public function canHaveFood(): bool
    {
        return $this->canHave(FoodDefinition::class);
    }

    public function canHaveFrozenFood(): bool
    {
        return $this->canHave(FrozenFoodDefinition::class);
    }

    public function canHaveWeight(?int $weight): bool
    {
        return true;
    }

    public function getAllowedDeliveryTypes(): array
    {
        return $this->getFromSchema('deliveryTypes') ?: [];
    }

    public function getAllowedInsuranceAmounts(): array
    {
        return $this->getShipmentOption(InsuranceDefinition::class) ?: [];
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
        $identifier = $this->getCarrier()->externalIdentifier;

        if (! isset($this->cache[$identifier])) {
            $this->cache[$identifier] = $this->createSchema();
        }

        return $this->cache[$identifier];
    }

    /**
     * @return bool
     */
    public function needsCustomerInfo(): bool
    {
        return (bool) $this->getFeature('needsCustomerInfo');
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
     * @return bool
     */
    protected function canHaveFeature(string $feature): bool
    {
        $value = $this->getFeature($feature);

        if (self::FEATURE_CUSTOM_CONTRACT_ONLY === $value) {
            return $this->carrier->isCustom;
        }

        return (bool) $value;
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
     * @param  class-string<OrderOptionDefinitionInterface>|OrderOptionDefinitionInterface $definition
     *
     * @return bool
     */
    private function canHave($definition): bool
    {
        return (bool) $this->getShipmentOption($definition);
    }

    private function createSchema(): array
    {
        /** @noinspection PhpUnhandledExceptionInspection */
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
     * @param  class-string<OrderOptionDefinitionInterface>|OrderOptionDefinitionInterface $definition
     *
     * @return mixed
     */
    private function getShipmentOption($definition)
    {
        $resolvedDefinition = $this->resolveDefinition($definition);

        return $this->getFromSchema(sprintf('shipmentOptions.%s', $resolvedDefinition->getShipmentOptionsKey()));
    }

    /**
     * @param  string $deliveryType
     *
     * @return bool
     */
    private function hasDeliveryType(string $deliveryType): bool
    {
        return in_array($deliveryType, $this->getAllowedDeliveryTypes(), true);
    }

    /**
     * @param  class-string<OrderOptionDefinitionInterface>|OrderOptionDefinitionInterface $definition
     *
     * @return \MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface
     */
    private function resolveDefinition($definition): OrderOptionDefinitionInterface
    {
        /** @var OrderOptionDefinitionInterface $resolvedDefinition */
        $resolvedDefinition = $definition instanceof OrderOptionDefinitionInterface
            ? $definition
            : new $definition();

        return $resolvedDefinition;
    }
}
