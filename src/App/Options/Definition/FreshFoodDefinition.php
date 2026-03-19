<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Options\Definition;

use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Validation\Validator\CarrierSchema;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesContractDefinitionsResponseOptionsOptionsV2;

final class FreshFoodDefinition implements OrderOptionDefinitionInterface
{
    public function getCarrierSettingsKey(): ?string
    {
        return CarrierSettings::EXPORT_FRESH_FOOD;
    }

    public function getProductSettingsKey(): ?string
    {
        return ProductSettings::EXPORT_FRESH_FOOD;
    }

    public function getShipmentOptionsKey(): ?string
    {
        return ShipmentOptions::FRESH_FOOD;
    }

    public function getCapabilitiesOptionsKey(): ?string
    {
        return null; // Not yet implemented in capabilities response
    }

    public function validate(CarrierSchema $carrierSchema): bool
    {
        return $carrierSchema->canHaveFreshFood();
    }
}
