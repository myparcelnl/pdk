<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Options\Definition;

use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Validation\Validator\CarrierSchema;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesContractDefinitionsResponseOptionsOptionsV2;

final class TrackedDefinition implements OrderOptionDefinitionInterface
{
    public function getCarrierSettingsKey(): ?string
    {
        return CarrierSettings::EXPORT_TRACKED;
    }

    public function getProductSettingsKey(): ?string
    {
        return ProductSettings::EXPORT_TRACKED;
    }

    public function getShipmentOptionsKey(): ?string
    {
        return ShipmentOptions::TRACKED;
    }

    /**
     * Note: The capabilities implementation is inverted:
     * - Tracking is ON by default
     * - When no_tracking is present, it is disabled
     * - In the definition, no_tracking means that it is possible to request no tracking.
     * - If the carrier does not support tracking, the option will not be available and tracking is always disabled anyway.
     * @return null|string
     */
    public function getCapabilitiesOptionsKey(): ?string
    {
        return RefCapabilitiesContractDefinitionsResponseOptionsOptionsV2::attributeMap()['no_tracking'];
    }

    public function validate(CarrierSchema $carrierSchema): bool
    {
        return $carrierSchema->canHaveTracked();
    }
}
