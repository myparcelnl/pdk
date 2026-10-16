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
     * The value to set when this option is off and the capabilities switch it on: because they make
     * it required, or because another option requires it.
     *
     * isEnabled() returns true for this value, but other values can count as on too. For insurance,
     * any amount above zero is on; this method gives the amount to start with.
     *
     * @param  null|\MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesResponseOptionsOptionV2|\MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesResponseOptionsInsuranceOptionV2 $capabilityOption
     *         This option's entry in the capabilities response for the shipment. Insurance gets a
     *         RefCapabilitiesResponseOptionsInsuranceOptionV2 with default, min and max amounts; the
     *         other options get a RefCapabilitiesResponseOptionsOptionV2. Null when the response has no
     *         entry for this option.
     *
     * @return int
     */
    public function getDefaultEnabledValue($capabilityOption): int;
}
