<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Options\Definition;

use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;
use MyParcelNL\Pdk\Validation\Validator\CarrierSchema;

final class FitInDigitalStampDefinition implements OrderOptionDefinitionInterface
{
    public function getCarrierSettingsKey(): ?string
    {
        return null;
    }

    public function getProductSettingsKey(): ?string
    {
        return ProductSettings::FIT_IN_DIGITAL_STAMP;
    }

    public function getShipmentOptionsKey(): ?string
    {
        return null;
    }

    public function validate(CarrierSchema $carrierSchema): bool
    {
        return $carrierSchema->canBeDigitalStamp();
    }
}
