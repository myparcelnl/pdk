<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Options\Definition;

use MyParcelNL\Pdk\Validation\Validator\CarrierSchema;

final class FitInDigitalStampDefinition extends AbstractOrderOptionDefinition
{
    public function getShipmentOptionsKey(): ?string
    {
        return null;
    }

    public function getCapabilitiesOptionsKey(): ?string
    {
        return null;
    }

    public function getProductSettingsKey(): ?string
    {
        return 'fitInDigitalStamp';
    }

    public function getAllowSettingsKey(): ?string
    {
        return null;
    }

    public function getPriceSettingsKey(): ?string
    {
        return null;
    }

    public function validate(CarrierSchema $carrierSchema): bool
    {
        return $carrierSchema->canBeDigitalStamp();
    }
}
