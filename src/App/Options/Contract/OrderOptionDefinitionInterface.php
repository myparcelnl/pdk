<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Options\Contract;

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
     * Get the cast type for this option on the ShipmentOptions model.
     *
     * @return string
     */
    public function getShipmentOptionsCast(): string;

    /**
     * Get the default value for this option on the ShipmentOptions model.
     *
     * @return mixed
     */
    public function getShipmentOptionsDefault();

    /**
     * Whether the given shipment options value switches this option on. Use it to decide if the
     * requires and excludes of this option apply.
     *
     * @param  mixed $value
     *
     * @return bool
     */
    public function isEnabled($value): bool;

    /**
     * The shipment options value that switches this option on. Use it when the capabilities make the
     * option required, or when another option requires it.
     *
     * @param  mixed $capabilityOption The option from the capabilities response, if there is one.
     *
     * @return int
     */
    public function getEnabledValue($capabilityOption): int;
}
