<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Validation\Validator;

use MyParcelNL\Pdk\Validation\Contract\DeliveryOptionsValidatorInterface;
use MyParcelNL\Pdk\Validation\Contract\SchemaInterface;
use MyParcelNL\Pdk\Validation\Repository\SchemaRepository;

abstract class OrderPropertiesValidator implements SchemaInterface, DeliveryOptionsValidatorInterface
{
    public const  WEIGHT_KEY           = 'properties.physicalProperties.properties.weight';
    private const DELIVERY_OPTIONS_KEY = 'properties.deliveryOptions.properties';
    private const SHIPMENT_OPTIONS_KEY = self::DELIVERY_OPTIONS_KEY . '.shipmentOptions.properties';

    /**
     * @var \MyParcelNL\Pdk\Validation\Repository\SchemaRepository
     */
    protected $repository;

    /**
     * @param  \MyParcelNL\Pdk\Validation\Repository\SchemaRepository $repository
     */
    public function __construct(SchemaRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return bool
     */
    public function canHaveAgeCheck(): bool
    {
        return $this->canHaveOption(sprintf('%s.ageCheck', self::SHIPMENT_OPTIONS_KEY));
    }

    /**
     * @return bool
     */
    public function canHaveDate(): bool
    {
        return $this->canHaveOption(sprintf('%s.date', self::DELIVERY_OPTIONS_KEY));
    }

    /**
     * @return bool
     */
    public function canHaveDirectReturn(): bool
    {
        return $this->canHaveOption(sprintf('%s.return', self::SHIPMENT_OPTIONS_KEY));
    }

    /**
     * @return bool
     */
    public function canHaveEveningDelivery(): bool
    {
        return $this->canHaveOption(sprintf('%s.morningDelivery', self::DELIVERY_OPTIONS_KEY));
    }

    /**
     * @param  null|int $amount
     *
     * @return bool
     */
    public function canHaveInsurance(?int $amount = 10000): bool
    {
        return $this->canHaveOption(sprintf('%s.insurance', self::SHIPMENT_OPTIONS_KEY), $amount);
    }

    /**
     * @return bool
     */
    public function canHaveLargeFormat(): bool
    {
        return $this->canHaveOption(sprintf('%s.largeFormat', self::SHIPMENT_OPTIONS_KEY));
    }

    /**
     * @return bool
     */
    public function canHaveMorningDelivery(): bool
    {
        return $this->canHaveOption(sprintf('%s.morningDelivery', self::DELIVERY_OPTIONS_KEY));
    }

    /**
     * @return bool
     */
    public function canHaveMultiCollo(): bool
    {
        return $this->canHaveOption('properties.multiCollo');
    }

    /**
     * @return bool
     */
    public function canHaveOnlyRecipient(): bool
    {
        return $this->canHaveOption(sprintf('%s.onlyRecipient', self::SHIPMENT_OPTIONS_KEY));
    }

    /**
     * @return bool
     */
    public function canHavePickup(): bool
    {
        return $this->canHaveOption(sprintf('%s.pickup', self::DELIVERY_OPTIONS_KEY));
    }

    /**
     * @return bool
     */
    public function canHaveSameDayDelivery(): bool
    {
        return $this->canHaveOption(sprintf('%s.sameDayDelivery', self::DELIVERY_OPTIONS_KEY));
    }

    /**
     * @return bool
     */
    public function canHaveSignature(): bool
    {
        return $this->canHaveOption(sprintf('%s.signature', self::SHIPMENT_OPTIONS_KEY));
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
    public function getAllowedInsuranceAmounts(): array
    {
        return $this->repository->getValidOptions(
            $this->getSchema(),
            sprintf('%s.insurance', self::SHIPMENT_OPTIONS_KEY)
        );
    }

    public function getAllowedPackageTypes(): array
    {
        return $this->repository->getValidOptions(
            $this->getSchema(),
            self::SHIPMENT_OPTIONS_KEY . '.properties.packageType'
        );
    }

    /**
     * @param  string $option
     * @param         $value
     *
     * @return bool
     */
    protected function canHaveOption(string $option, $value = null): bool
    {
        return $this->repository->validateOption($this->getSchema(), $option, $value);
    }
}
