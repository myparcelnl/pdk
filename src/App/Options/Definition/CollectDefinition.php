<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Options\Definition;

use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\Proposition\Model\PropositionCarrierFeatures;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Validation\Validator\CarrierSchema;

class CollectDefinition implements OrderOptionDefinitionInterface
{
    /**
     * @inheritDoc
     */
    public function getCarrierSettingsKey(): ?string
    {
        return CarrierSettings::EXPORT_COLLECT;
    }

    /**
     * @inheritDoc
     */
    public function getProductSettingsKey(): ?string
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getShipmentOptionsKey(): ?string
    {
        return ShipmentOptions::COLLECT;
    }

    public function getPropositionKey(): ?string
    {
        return PropositionCarrierFeatures::SHIPMENT_OPTION_COLLECT_NAME;
    }

    /**
     * @inheritDoc
     */
    public function validate(CarrierSchema $carrierSchema): bool
    {
        return $carrierSchema->canHaveCollect();
    }
}
