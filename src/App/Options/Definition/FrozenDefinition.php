<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Options\Definition;

use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Validation\Validator\CarrierSchema;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesContractDefinitionsResponseOptionsOptionsV2;

final class FrozenDefinition implements OrderOptionDefinitionInterface
{
    public function getCarrierSettingsKey(): ?string
    {
        return CarrierSettings::EXPORT_FROZEN;
    }

    public function getProductSettingsKey(): ?string
    {
        return ProductSettings::EXPORT_FROZEN;
    }

    public function getShipmentOptionsKey(): ?string
    {
        return ShipmentOptions::FROZEN;
    }

    public function getCapabilitiesOptionsKey(): ?string
    {
        return RefCapabilitiesContractDefinitionsResponseOptionsOptionsV2::attributeMap()['frozen'];
    }

    public function validate(CarrierSchema $carrierSchema): bool
    {
        return $carrierSchema->canHaveFrozen();
    }
}
