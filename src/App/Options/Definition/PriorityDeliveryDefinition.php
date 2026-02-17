<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Options\Definition;

use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\Proposition\Model\PropositionCarrierFeatures;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Validation\Validator\CarrierSchema;

final class PriorityDeliveryDefinition implements OrderOptionDefinitionInterface
{
    public function getCarrierSettingsKey(): ?string
    {
        return CarrierSettings::ALLOW_PRIORITY_DELIVERY;
    }

    public function getProductSettingsKey(): ?string
    {
        return null;
    }

    public function getShipmentOptionsKey(): ?string
    {
        return ShipmentOptions::PRIORITY_DELIVERY;
    }

    public function getPropositionKey(): ?string
    {
        return PropositionCarrierFeatures::SHIPMENT_OPTION_PRIORITY_DELIVERY_NAME;
    }

    public function validate(CarrierSchema $carrierSchema): bool
    {
        return $carrierSchema->hasShipmentOptionName(
            PropositionCarrierFeatures::SHIPMENT_OPTION_PRIORITY_DELIVERY_NAME
        );
    }
}
