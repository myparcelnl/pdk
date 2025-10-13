<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Options\Contract;

use MyParcelNL\Pdk\Validation\Validator\CarrierSchema;

interface OrderOptionDefinitionInterface
{
    /**
     * Get the key that represents the option in the carrier settings.
     *
     * @return null|string
     */
    public function getCarrierSettingsKey(): ?string;

    /**
     * Get the key that represents the option in the product settings.
     *
     * @return null|string
     */
    public function getProductSettingsKey(): ?string;

    /**
     * Get the key that represents the option in the shipment options.
     *
     * @return null|string
     */
    public function getShipmentOptionsKey(): ?string;

    /**
     * Get the key that represents the option in the proposition.
     *
     * @return null|string
     */
    public function getPropositionKey(): ?string;

    /**
     * Validates if the option is allowed for the current carrier.
     *
     * @param  \MyParcelNL\Pdk\Validation\Validator\CarrierSchema $carrierSchema
     *
     * @return bool
     */
    public function validate(CarrierSchema $carrierSchema): bool;
}
