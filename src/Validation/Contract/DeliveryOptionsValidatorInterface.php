<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Validation\Contract;

interface DeliveryOptionsValidatorInterface
{
    public function canHaveEveningDelivery(): bool;

    public function canHaveMorningDelivery(): bool;

    public function canHaveMultiCollo(): bool;

    public function canHavePickup(): bool;

    public function canHaveStandardDelivery(): bool;

    public function canHaveWeight(?int $weight): bool;

    public function getAllowedDeliveryTypes(): array;

    public function getAllowedInsuranceAmounts(): array;

    public function getAllowedPackageTypes(): array;
}
