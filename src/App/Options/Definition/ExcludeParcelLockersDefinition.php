<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Options\Definition;

use MyParcelNL\Pdk\Validation\Validator\CarrierSchema;

final class ExcludeParcelLockersDefinition extends AbstractOrderOptionDefinition
{
    public function getShipmentOptionsKey(): ?string
    {
        return 'excludeParcelLockers';
    }

    public function getCapabilitiesOptionsKey(): ?string
    {
        return null;
    }

    public function getCarrierSettingsKey(): ?string
    {
        return null;
    }

    public function getProductSettingsKey(): ?string
    {
        return 'excludeParcelLockers';
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
        // This option is always available for all carriers
        return true;
    }
}
