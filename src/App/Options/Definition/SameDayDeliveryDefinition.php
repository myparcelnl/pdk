<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Options\Definition;

use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Validation\Validator\CarrierSchema;

final class SameDayDeliveryDefinition implements OrderOptionDefinitionInterface
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
        return ShipmentOptions::SAME_DAY_DELIVERY;
    }

    public function validate(CarrierSchema $carrierSchema): bool
    {
        return $carrierSchema->canHaveSameDayDelivery();
    }
}
