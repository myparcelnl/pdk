<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Options\Definition;

use MyParcelNL\Pdk\Types\Service\TriStateService;
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
}
