<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Options\Definition;

use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Validation\Validator\CarrierSchema;

final class OnlyRecipientDefinition implements OrderOptionDefinitionInterface
{
    public function getCarrierSettingsKey(): ?string
    {
        return CarrierSettings::EXPORT_ONLY_RECIPIENT;
    }

    public function getProductSettingsKey(): ?string
    {
        return ProductSettings::EXPORT_ONLY_RECIPIENT;
    }

    public function getShipmentOptionsKey(): ?string
    {
        return ShipmentOptions::ONLY_RECIPIENT;
    }

    public function validate(CarrierSchema $carrierSchema): bool
    {
        return $carrierSchema->canHaveOnlyRecipient();
    }
}
