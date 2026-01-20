<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Options\Definition;

use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Validation\Validator\CarrierSchema;

final class ExcludeParcelLockersDefinition implements OrderOptionDefinitionInterface
{
    public function getPropositionKey(): ?string
    {
        return null;
    }

    public function getCarrierSettingsKey(): ?string
    {
        return null;
    }

    public function getProductSettingsKey(): ?string
    {
        return ProductSettings::EXCLUDE_PARCEL_LOCKERS;
    }

    public function getShipmentOptionsKey(): ?string
    {
        return ShipmentOptions::EXCLUDE_PARCEL_LOCKERS;
    }

    public function validate(CarrierSchema $carrierSchema): bool
    {
        // This option is always available for all carriers
        return true;
    }
}
