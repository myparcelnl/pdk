<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Options\Definition;

use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use MyParcelNL\Pdk\Validation\Validator\CarrierSchema;

abstract class AbstractOrderOptionDefinition implements OrderOptionDefinitionInterface
{
    /**
     * The internal PDK key used on the ShipmentOptions model (e.g. 'signature', 'ageCheck').
     * This is the root key from which carrier/product/allow/price settings keys are derived.
     * These keys correspond to the legacy API naming used by the shipment-, order v1,
     * delivery-options and other legacy API endpoints.
     *
     * By default, derived from the SDK's RefShipmentShipmentOptions::attributeMap() via
     * Str::camel(). Override with a string if the PDK key differs from the SDK key's
     * camelCase equivalent, or return null if this definition does not represent a shipment
     * option (e.g. product-only settings like CountryOfOrigin).
     *
     * When null, all derived settings keys also return null automatically, and the option
     * will not appear on the ShipmentOptions model.
     */
    abstract public function getShipmentOptionsKey(): ?string;

    /**
     * The SDK capabilities key (e.g. 'requiresSignature', 'oversizedPackage').
     * This is the explicit bridge between PDK option names and SDK-generated type names.
     * These keys correspond to the V2 naming used by the capabilities API and
     * microservices (e.g. order v2).
     *
     * Return null if this option has no corresponding capabilities entry (e.g.
     * ExcludeParcelLockers). When null, the option cannot be validated against carrier
     * capabilities, and no default value will be resolved from the capabilities response.
     */
    abstract public function getCapabilitiesOptionsKey(): ?string;

    /**
     * The carrier-level settings key (e.g. 'exportSignature').
     * Derived by default: 'export' + ucfirst(shipmentOptionsKey).
     *
     * Return null to opt out of carrier-level settings. When null, no attribute will be
     * registered on CarrierSettings and the option cannot be configured at the carrier level.
     */
    public function getCarrierSettingsKey(): ?string
    {
        $key = $this->getShipmentOptionsKey();

        return $key ? 'export' . ucfirst($key) : null;
    }

    /**
     * The product-level settings key (e.g. 'exportSignature').
     * Derived by default: same as carrier settings key.
     *
     * Return null to opt out of product-level settings. When null, no attribute will be
     * registered on ProductSettings and the option cannot be overridden per product.
     */
    public function getProductSettingsKey(): ?string
    {
        return $this->getCarrierSettingsKey();
    }

    /**
     * The delivery options "allow" toggle key (e.g. 'allowSignature').
     * Derived by default: 'allow' + ucfirst(shipmentOptionsKey).
     *
     * This controls whether the consumer can choose this option at checkout in the
     * delivery options widget. Only use for options where consumer choice is appropriate
     * (e.g. signature, pickup). Return null for options that should always be applied by
     * the merchant via the export setting and should not be (de)selectable by the consumer
     * (e.g. age check, insurance, hide sender).
     *
     * When null, no allow attribute will be registered on CarrierSettings and the option
     * will not appear as a toggleable choice in the delivery options frontend widget.
     */
    public function getAllowSettingsKey(): ?string
    {
        $key = $this->getShipmentOptionsKey();

        return $key ? 'allow' . ucfirst($key) : null;
    }

    /**
     * The price surcharge key (e.g. 'priceSignature').
     * Derived by default: 'price' + ucfirst(shipmentOptionsKey).
     *
     * Return null to opt out of the price surcharge. When null, no price attribute will be
     * registered on CarrierSettings and no surcharge will be shown in the delivery options
     * frontend widget for this option.
     */
    public function getPriceSettingsKey(): ?string
    {
        $key = $this->getShipmentOptionsKey();

        return $key ? 'price' . ucfirst($key) : null;
    }

    /**
     * The cast type for this option on the ShipmentOptions model.
     * Default: TriStateService::TYPE_STRICT (tri-state: -1/0/1).
     *
     * Override for options with different value types (e.g. InsuranceDefinition returns 'int').
     * Fulfilment models derive their cast from this: TYPE_STRICT becomes 'bool', others are kept as-is.
     */
    public function getShipmentOptionsCast(): string
    {
        return TriStateService::TYPE_STRICT;
    }

    /**
     * The default value for this option on the ShipmentOptions model.
     * Default: TriStateService::INHERIT (-1).
     *
     * Override for options with different default values.
     * Fulfilment models use null as default regardless of this value.
     *
     * @return mixed
     */
    public function getShipmentOptionsDefault()
    {
        return TriStateService::INHERIT;
    }

    /**
     * Validates whether this option is available for the given carrier.
     * Default: checks if the capabilities key exists in the carrier's options.
     *
     * Override to provide custom validation logic, or to always return true for options
     * that are universally available regardless of carrier capabilities.
     */
    public function validate(CarrierSchema $carrierSchema): bool
    {
        return $carrierSchema->canHaveShipmentOption($this);
    }
}
