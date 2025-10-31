<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Options\Definition;

use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Validation\Validator\CarrierSchema;

final class FrozenFoodDefinition implements OrderOptionDefinitionInterface
{
    public function getCarrierSettingsKey(): ?string
    {
        return CarrierSettings::EXPORT_FROZEN_FOOD;
    }

    public function getProductSettingsKey(): ?string
    {
        return ProductSettings::EXPORT_FROZEN_FOOD;
    }

    public function getShipmentOptionsKey(): ?string
    {
        return ShipmentOptions::FROZEN_FOOD;
    }

    public function validate(CarrierSchema $carrierSchema): bool
    {
        return $carrierSchema->canHaveFrozenFood();
    }
}
