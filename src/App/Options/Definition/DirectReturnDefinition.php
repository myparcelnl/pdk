<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Options\Definition;

use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\Proposition\Model\PropositionCarrierFeatures;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Validation\Validator\CarrierSchema;

final class DirectReturnDefinition implements OrderOptionDefinitionInterface
{
    public function getCarrierSettingsKey(): ?string
    {
        return CarrierSettings::EXPORT_RETURN;
    }

    public function getProductSettingsKey(): ?string
    {
        return ProductSettings::EXPORT_RETURN;
    }

    public function getShipmentOptionsKey(): ?string
    {
        return ShipmentOptions::DIRECT_RETURN;
    }

    public function getPropositionKey(): ?string
    {
        return PropositionCarrierFeatures::SHIPMENT_OPTION_DIRECT_RETURN_NAME;
    }

    public function validate(CarrierSchema $carrierSchema): bool
    {
        return $carrierSchema->canHaveDirectReturn();
    }
}
