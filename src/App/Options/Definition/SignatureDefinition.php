<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Options\Definition;

use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\Proposition\Model\PropositionCarrierFeatures;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Validation\Validator\CarrierSchema;

final class SignatureDefinition implements OrderOptionDefinitionInterface
{
    public function getCarrierSettingsKey(): ?string
    {
        return CarrierSettings::EXPORT_SIGNATURE;
    }

    public function getProductSettingsKey(): ?string
    {
        return ProductSettings::EXPORT_SIGNATURE;
    }

    public function getShipmentOptionsKey(): ?string
    {
        return ShipmentOptions::SIGNATURE;
    }

    public function getPropositionKey(): ?string
    {
        return PropositionCarrierFeatures::SHIPMENT_OPTION_SIGNATURE_NAME;
    }

    public function validate(CarrierSchema $carrierSchema): bool
    {
        return $carrierSchema->canHaveSignature();
    }
}
