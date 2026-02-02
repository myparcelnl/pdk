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
use MyParcelNL\Pdk\App\Options\Definition\PriorityDeliveryDefinition;
use MyParcelNL\Pdk\App\Options\Definition\ReceiptCodeDefinition;
use MyParcelNL\Pdk\App\Options\Definition\SameDayDeliveryDefinition;
use MyParcelNL\Pdk\App\Options\Definition\SignatureDefinition;
use MyParcelNL\Pdk\App\Options\Definition\TrackedDefinition;
use MyParcelNL\Pdk\App\Options\Definition\FreshFoodDefinition;
use MyParcelNL\Pdk\App\Options\Definition\FrozenDefinition;
use MyParcelNL\Pdk\App\Options\Definition\MondayDeliveryDefinition;
use MyParcelNL\Pdk\App\Options\Definition\SaturdayDeliveryDefinition;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Proposition\Model\PropositionCarrierMetadata;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Validation\Contract\DeliveryOptionsValidatorInterface;

/**
 * @deprecated Should switch to proposition-related functionality in the future
 * @package MyParcelNL\Pdk\Validation\Validator
 */
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

    /**
     * Given a Shipment Option Name from the Proposition config, return whether that's enabled in the schema.
     *
     * @todo this should use ENUMs in the future.
     * @param string $shipmentOptionName
     * @return bool
     * @throws BadMethodCallException
     */
    public function hasShipmentOptionName(string $shipmentOptionName): bool
    {
        return in_array(
            $shipmentOptionName,
            $this->getFromSchema('shipmentOptions') ?: [],
        );
    }

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

    public function canHaveFreshFood(): bool
    {
        return $this->canHave(FreshFoodDefinition::class);
    }

    public function canHaveFrozen(): bool
    {
        return $this->canHave(FrozenDefinition::class);
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

    public function canHavePriorityDelivery(): bool
    {
        return $this->canHave(PriorityDeliveryDefinition::class);
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

    public function canHaveSaturdayDelivery(): bool
    {
        return $this->canHave(SaturdayDeliveryDefinition::class);
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
        $allowedAmounts = $this->getMetadataFeature('insuranceOptions');
        $hasOption = $this->hasShipmentOption(InsuranceDefinition::class);
        if (!$allowedAmounts && $hasOption) {
            Logger::warning(
                'Carrier schema does not have insurance options defined, but the carrier has the insurance option enabled.',
                [
                    'carrier' => $this->getCarrier()->externalIdentifier,
                ]
            );
            return [0];
        }
        return $hasOption ? $allowedAmounts : [];
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
        return (bool) $this->getMetadataFeature('needsCustomerInfo');
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
        $value = $this->getMetadataFeature($feature);

        if (PropositionCarrierMetadata::FEATURE_CUSTOM_CONTRACT_ONLY === $value) {
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
        return $this->hasShipmentOption($definition);
    }

    private function createSchema(): array
    {
        // Return the outbound features from the proposition config if present.
        return $this->getCarrier()->outboundFeatures ? $this->getCarrier()->outboundFeatures->toArray() : [];
    }

    public function hasMetadataFeature(string $feature): bool
    {
        $value = $this->getMetadataFeature($feature);

        if (PropositionCarrierMetadata::FEATURE_CUSTOM_CONTRACT_ONLY === $value) {
            return $this->carrier->isCustom;
        }

        return (bool) $value;
    }

    /**
     * @param  string $feature
     *
     * @return mixed
     */
    private function getMetadataFeature(string $feature)
    {
        return $this->getFromSchema(sprintf('metadata.%s', $feature));
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
    private function hasShipmentOption($definition)
    {
        $resolvedDefinition = $this->resolveDefinition($definition);

        return in_array(
            $resolvedDefinition->getPropositionKey(),
            $this->getFromSchema('shipmentOptions') ?: [],
        );
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
