<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Options\Definition;

use MyParcelNL\Pdk\Carrier\Util\InsuranceTierMath;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesResponseOptionsInsuranceOptionV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefShipmentShipmentOptions;
use MyParcelNL\Sdk\Support\Str;

final class InsuranceDefinition extends AbstractOrderOptionDefinition
{
    public function getShipmentOptionsKey(): ?string
    {
        return Str::camel(RefShipmentShipmentOptions::attributeMap()['insurance']);
    }

    public function getAllowSettingsKey(): ?string
    {
        return null;
    }

    public function getPriceSettingsKey(): ?string
    {
        return null;
    }

    public function getShipmentOptionsCast(): string
    {
        return 'int';
    }

    /**
     * Insurance is on when the shipment is insured for an amount above zero. ENABLED (1) also
     * counts: it means "insure, amount still to be calculated". Without this, an insured amount
     * would never apply the requires of insurance, and an amount of 0 is no insurance.
     *
     * @param  mixed $value
     *
     * @return bool
     */
    public function isEnabled($value): bool
    {
        return is_int($value) && $value > TriStateService::DISABLED;
    }

    /**
     * When the capabilities switch insurance on, insure for the carrier default. Without a default,
     * use the carrier minimum, then the lowest tier above zero.
     *
     * Without an amount above zero, or without an insurance entry, return 0: no insurance. Do not
     * return ENABLED (1) here. No calculator runs after this one to turn it into an amount, so the
     * shipment would be insured for 1 cent.
     *
     * @param  null|\MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesResponseOptionsInsuranceOptionV2 $capabilityOption
     *         The insurance entry in the capabilities response for the shipment, with its default, min
     *         and max amounts. Null when the response has no insurance entry.
     *
     * @return int
     */
    public function getDefaultEnabledValue($capabilityOption): int
    {
        if (! $capabilityOption instanceof RefCapabilitiesResponseOptionsInsuranceOptionV2) {
            return TriStateService::DISABLED;
        }

        $default = $capabilityOption->getDefault();
        $min     = $capabilityOption->getMin();
        $max     = $capabilityOption->getMax();

        $defaultAmount = $default ? $default->getAmount() : 0;
        $minAmount     = $min ? $min->getAmount() : 0;
        $maxAmount     = $max ? $max->getAmount() : 0;

        if ($defaultAmount > 0) {
            return $defaultAmount;
        }

        if ($minAmount > 0) {
            return $minAmount;
        }

        foreach (InsuranceTierMath::buildTiers($minAmount, $maxAmount) as $tier) {
            if ($tier > 0) {
                return $tier;
            }
        }

        return TriStateService::DISABLED;
    }
}
