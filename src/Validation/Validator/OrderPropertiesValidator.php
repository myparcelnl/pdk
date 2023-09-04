<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Validation\Validator;

use DateTimeImmutable;
use MyParcelNL\Pdk\App\Options\Definition\AgeCheckDefinition;
use MyParcelNL\Pdk\App\Options\Definition\DirectReturnDefinition;
use MyParcelNL\Pdk\App\Options\Definition\HideSenderDefinition;
use MyParcelNL\Pdk\App\Options\Definition\InsuranceDefinition;
use MyParcelNL\Pdk\App\Options\Definition\LargeFormatDefinition;
use MyParcelNL\Pdk\App\Options\Definition\OnlyRecipientDefinition;
use MyParcelNL\Pdk\App\Options\Definition\SameDayDeliveryDefinition;
use MyParcelNL\Pdk\App\Options\Definition\SignatureDefinition;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use MyParcelNL\Pdk\Validation\Contract\DeliveryOptionsValidatorInterface;
use MyParcelNL\Pdk\Validation\Contract\SchemaInterface;
use MyParcelNL\Pdk\Validation\Repository\SchemaRepository;

abstract class OrderPropertiesValidator implements SchemaInterface, DeliveryOptionsValidatorInterface
{
    public const  WEIGHT_KEY           = 'properties.physicalProperties.properties.weight';
    private const DELIVERY_OPTIONS_KEY = 'properties.deliveryOptions.properties';
    private const SHIPMENT_OPTIONS_KEY = self::DELIVERY_OPTIONS_KEY . '.shipmentOptions.properties';

    /**
     * @var null|string
     */
    protected $description;

    /**
     * @var array
     */
    protected $errors;

    /**
     * @var \MyParcelNL\Pdk\Validation\Repository\SchemaRepository
     */
    protected $repository;

    public function __construct()
    {
        $this->repository = Pdk::get(SchemaRepository::class);
    }

    /**
     * @return bool
     */
    public function canHaveAgeCheck(): bool
    {
        return $this->canHaveOptionDef(AgeCheckDefinition::class, self::SHIPMENT_OPTIONS_KEY);
    }

    /**
     * @return bool
     */
    public function canHaveDate(): bool
    {
        $testDate = (new DateTimeImmutable())->format(Pdk::get('defaultDateFormat'));

        return $this->canHaveOption([self::DELIVERY_OPTIONS_KEY, DeliveryOptions::DATE], $testDate);
    }

    /**
     * @return bool
     */
    public function canHaveDirectReturn(): bool
    {
        return $this->canHaveOptionDef(DirectReturnDefinition::class, self::SHIPMENT_OPTIONS_KEY);
    }

    /**
     * @return bool
     */
    public function canHaveEveningDelivery(): bool
    {
        return in_array(DeliveryOptions::DELIVERY_TYPE_EVENING_NAME, $this->getAllowedDeliveryTypes(), true);
    }

    /**
     * @return bool
     */
    public function canHaveHideSender(): bool
    {
        return $this->canHaveOptionDef(HideSenderDefinition::class, self::SHIPMENT_OPTIONS_KEY);
    }

    /**
     * @param  null|int $amount
     *
     * @return bool
     */
    public function canHaveInsurance(?int $amount = 10000): bool
    {
        return $this->canHaveOptionDef(InsuranceDefinition::class, self::SHIPMENT_OPTIONS_KEY, $amount);
    }

    /**
     * @return bool
     */
    public function canHaveLargeFormat(): bool
    {
        return $this->canHaveOptionDef(LargeFormatDefinition::class, self::SHIPMENT_OPTIONS_KEY);
    }

    /**
     * @return bool
     */
    public function canHaveMorningDelivery(): bool
    {
        return in_array(DeliveryOptions::DELIVERY_TYPE_MORNING_NAME, $this->getAllowedDeliveryTypes(), true);
    }

    /**
     * Check if label amount can be more than 1
     *
     * @return bool
     */
    public function canHaveMultiCollo(): bool
    {
        return $this->canHaveOption([self::DELIVERY_OPTIONS_KEY, DeliveryOptions::LABEL_AMOUNT], 2);
    }

    /**
     * @return bool
     */
    public function canHaveOnlyRecipient(): bool
    {
        return $this->canHaveOptionDef(OnlyRecipientDefinition::class, self::SHIPMENT_OPTIONS_KEY);
    }

    /**
     * @return bool
     */
    public function canHavePickup(): bool
    {
        return in_array(DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME, $this->getAllowedDeliveryTypes(), true);
    }

    /**
     * @return bool
     */
    public function canHaveSameDayDelivery(): bool
    {
        return $this->canHaveOptionDef(SameDayDeliveryDefinition::class, self::SHIPMENT_OPTIONS_KEY);
    }

    /**
     * @return bool
     */
    public function canHaveSignature(): bool
    {
        return $this->canHaveOptionDef(SignatureDefinition::class, self::SHIPMENT_OPTIONS_KEY);
    }

    /**
     * @param  null|int $weight
     *
     * @return bool
     */
    public function canHaveWeight(?int $weight = 10): bool
    {
        return $this->canHaveOption(self::WEIGHT_KEY, $weight);
    }

    /**
     * @return array
     */
    public function getAllowedDeliveryTypes(): array
    {
        return $this->getValidOptions(sprintf('%s.deliveryType', self::DELIVERY_OPTIONS_KEY));
    }

    public function getAllowedInsuranceAmounts(): array
    {
        return $this->getValidOptions($this->getKey(InsuranceDefinition::class, self::SHIPMENT_OPTIONS_KEY));
    }

    public function getAllowedPackageTypes(): array
    {
        return $this->getValidOptions(sprintf('%s.properties.packageType', self::SHIPMENT_OPTIONS_KEY));
    }

    /**
     * @return null|string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param  string|string[] $option
     * @param  mixed           $value
     *
     * @return bool
     */
    protected function canHaveOption($option, $value = TriStateService::ENABLED): bool
    {
        return $this->repository->validateOption($this->getSchema(), implode('.', Arr::wrap($option)), $value);
    }

    /**
     * @param  class-string<\MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface> $definitionClass
     * @param  string                                                                            $prefix
     * @param  mixed                                                                             $value
     *
     * @return bool
     */
    protected function canHaveOptionDef(
        string $definitionClass,
        string $prefix = '',
               $value = TriStateService::ENABLED
    ): bool {
        return $this->canHaveOption($this->getKey($definitionClass, $prefix), $value);
    }

    /**
     * @param  string $definitionClass
     * @param  string $prefix
     *
     * @return string
     */
    private function getKey(string $definitionClass, string $prefix): string
    {
        /** @var \MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface $instance */
        $instance = new $definitionClass();

        return sprintf('%s.%s', $prefix, $instance->getShipmentOptionsKey());
    }

    /**
     * @param  string $key
     *
     * @return array
     */
    private function getValidOptions(string $key): array
    {
        return $this->repository->getValidOptions($this->getSchema(), $key);
    }
}
