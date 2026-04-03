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
     * Get the camelCase property key that represents this option in the capabilities options object
     * (RefCapabilitiesContractDefinitionsResponseOptionsOptionsV2).
     *
     * @return null|string
     */
    public function getCapabilitiesOptionsKey(): ?string;

    /**
     * Get the delivery options "allow" toggle key (e.g. 'allowSignature').
     *
     * @return null|string
     */
    public function getAllowSettingsKey(): ?string;

    /**
     * Get the price surcharge key (e.g. 'priceSignature').
     *
     * @return null|string
     */
    public function getPriceSettingsKey(): ?string;

    /**
     * Validates if the option is allowed for the current carrier.
     *
     * @param  \MyParcelNL\Pdk\Validation\Validator\CarrierSchema $carrierSchema
     *
     * @return bool
     */
    public function validate(CarrierSchema $carrierSchema): bool;
}
