<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Validation\Contract;

interface DeliveryOptionsValidatorInterface
{
    public function canHaveAgeCheck(): bool;

    public function canHaveDate(): bool;

    public function canHaveDirectReturn(): bool;

    public function canHaveEveningDelivery(): bool;

    public function canHaveHideSender(): bool;

    public function canHaveInsurance(?int $amount): bool;

    public function canHaveLargeFormat(): bool;

    public function canHaveMorningDelivery(): bool;

    public function canHaveMultiCollo(): bool;

    public function canHaveOnlyRecipient(): bool;

    public function canHavePickup(): bool;

    public function canHaveSameDayDelivery(): bool;

    public function canHaveSignature(): bool;

    public function canHaveWeight(?int $weight): bool;

    public function getAllowedDeliveryTypes(): array;

    public function getAllowedInsuranceAmounts(): array;

    public function getAllowedPackageTypes(): array;
}
