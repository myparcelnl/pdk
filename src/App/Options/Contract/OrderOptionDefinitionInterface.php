<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Options\Contract;

use MyParcelNL\Pdk\Validation\Validator\CarrierSchema;

interface OrderOptionDefinitionInterface
{
    /**
     * Get the key that represents the option in the carrier settings.
     */
    public function getCarrierSettingsKey(): ?string;

    /**
     * Get the key that represents the option in the product settings.
     */
    public function getProductSettingsKey(): ?string;

    /**
     * Get the key that represents the option in the shipment options.
     */
    public function getShipmentOptionsKey(): ?string;

    /**
     * Validates if the option is allowed for the current carrier.
     */
    public function validate(CarrierSchema $carrierSchema): bool;
}
