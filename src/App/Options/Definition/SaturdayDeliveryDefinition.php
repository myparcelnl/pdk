<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Options\Definition;

use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\Proposition\Model\PropositionCarrierFeatures;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Validation\Validator\CarrierSchema;

final class SaturdayDeliveryDefinition implements OrderOptionDefinitionInterface
{
    public function getCarrierSettingsKey(): ?string
    {
        return null;
    }

    public function getProductSettingsKey(): ?string
    {
        return null;
    }

    public function getShipmentOptionsKey(): ?string
    {
        return ShipmentOptions::SATURDAY_DELIVERY;
    }

    public function getPropositionKey(): ?string
    {
        return PropositionCarrierFeatures::SHIPMENT_OPTION_SATURDAY_DELIVERY_NAME;
    }

    public function validate(CarrierSchema $carrierSchema): bool
    {
        return $carrierSchema->canHaveSaturdayDelivery();
    }
}
