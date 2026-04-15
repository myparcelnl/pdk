<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\General;

use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Carrier\Service\CapabilitiesValidationService;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesResponseCapabilityV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesResponseOptionsOptionsV2;

/**
 * Applies requires/excludes/isRequired from the capabilities API response to shipment options.
 * Carrier settings (allowX) take precedence over capabilities.
 */
final class CapabilitiesOptionCalculator extends AbstractPdkOrderOptionCalculator
{
    /**
     * @var \MyParcelNL\Pdk\Carrier\Service\CapabilitiesValidationService
     */
    private $capabilitiesService;

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrder $order
     */
    public function __construct(PdkOrder $order)
    {
        parent::__construct($order);

        $this->capabilitiesService = Pdk::get(CapabilitiesValidationService::class);
    }

    /**
     * @return void
     */
    public function calculate(): void
    {
        $carrier = $this->order->deliveryOptions->carrier;

        if (! $carrier) {
            return;
        }

        $capability = $this->getCapability();

        if (! $capability) {
            return;
        }

        $this->setContractId($capability);

        $options        = $capability->getOptions();
        $carrierSettings = CarrierSettings::fromCarrier($carrier);

        /** @var OrderOptionDefinitionInterface[] $definitions */
        $definitions = Pdk::get('orderOptionDefinitions');

        // Index definitions by capabilities key for requires/excludes lookups.
        $definitionsByCapKey = $this->indexDefinitionsByCapabilitiesKey($definitions);

        // First pass: apply carrier settings and capabilities constraints.
        foreach ($definitions as $definition) {
            $this->applyDefinition($definition, $options, $carrierSettings);
        }

        // Second pass: propagate requires/excludes for enabled options.
        $this->propagateConstraints($definitions, $options, $definitionsByCapKey);
    }

    /**
     * @return null|\MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesResponseCapabilityV2
     */
    private function getCapability(): ?RefCapabilitiesResponseCapabilityV2
    {
        $deliveryOptions = $this->order->deliveryOptions;
        $carrierName     = $deliveryOptions->carrier->carrier;
        $cc              = $this->order->shippingAddress->cc;
        $v2PackageType   = DeliveryOptions::PACKAGE_TYPES_V2_MAP[$deliveryOptions->packageType] ?? null;
        $v2DeliveryType  = DeliveryOptions::DELIVERY_TYPES_V2_MAP[$deliveryOptions->deliveryType] ?? null;

        if (! $v2PackageType) {
            return null;
        }

        $capabilities = $this->capabilitiesService->getCapabilitiesForOrderContext(
            $carrierName,
            $cc,
            $v2PackageType,
            $v2DeliveryType
        );

        return $capabilities[$carrierName] ?? null;
    }

    /**
     * @param  \MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesResponseCapabilityV2 $capability
     *
     * @return void
     */
    private function setContractId(RefCapabilitiesResponseCapabilityV2 $capability): void
    {
        $contract = $capability->getContract();

        if ($contract) {
            $this->order->deliveryOptions->contractId = $contract->getId();
        }
    }

    /**
     * Apply carrier settings and capabilities constraints for a single definition.
     *
     * @param  \MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface                    $definition
     * @param  \MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesResponseOptionsOptionsV2 $options
     * @param  \MyParcelNL\Pdk\Settings\Model\CarrierSettings                                        $carrierSettings
     *
     * @return void
     */
    private function applyDefinition(
        OrderOptionDefinitionInterface $definition,
        RefCapabilitiesResponseOptionsOptionsV2 $options,
        CarrierSettings $carrierSettings
    ): void {
        $shipmentKey     = $definition->getShipmentOptionsKey();
        $capabilitiesKey = $definition->getCapabilitiesOptionsKey();

        if (! $shipmentKey || ! $capabilitiesKey) {
            return;
        }

        // Carrier settings take precedence: merchant's allowX toggle overrides everything.
        $allowKey = $definition->getAllowSettingsKey();

        if ($allowKey && $carrierSettings->getAttribute($allowKey) === false) {
            $this->forceOption($shipmentKey, TriStateService::DISABLED);

            return;
        }

        // Capabilities determine what's valid for this shipment context.
        $optionValue = $this->getCapabilityOption($options, $capabilitiesKey);

        if (! $optionValue) {
            $this->forceOption($shipmentKey, TriStateService::DISABLED);

            return;
        }

        if ($optionValue->getIsRequired()) {
            $this->forceOption($shipmentKey, TriStateService::ENABLED);
        }
    }

    /**
     * Propagate requires/excludes constraints for all enabled options, cascading through the dependency chain.
     *
     * @param  \MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface[]                  $definitions
     * @param  \MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesResponseOptionsOptionsV2 $options
     * @param  array<string, OrderOptionDefinitionInterface>                                           $definitionsByCapKey
     *
     * @return void
     */
    private function propagateConstraints(
        array $definitions,
        RefCapabilitiesResponseOptionsOptionsV2 $options,
        array $definitionsByCapKey
    ): void {
        foreach ($definitions as $definition) {
            $shipmentKey     = $definition->getShipmentOptionsKey();
            $capabilitiesKey = $definition->getCapabilitiesOptionsKey();

            if (! $shipmentKey || ! $capabilitiesKey) {
                continue;
            }

            $currentValue = $this->order->deliveryOptions->shipmentOptions->getAttribute($shipmentKey);

            if ($currentValue !== TriStateService::ENABLED) {
                continue;
            }

            $this->applyRequiresChain($capabilitiesKey, $options, $definitionsByCapKey, []);

            $optionValue = $this->getCapabilityOption($options, $capabilitiesKey);

            if (! $optionValue) {
                continue;
            }

            foreach ($optionValue->getExcludes() ?? [] as $excludedCapKey) {
                $excludedDef = $definitionsByCapKey[$excludedCapKey] ?? null;

                if ($excludedDef && $excludedDef->getShipmentOptionsKey()) {
                    $this->forceOption($excludedDef->getShipmentOptionsKey(), TriStateService::DISABLED);
                }
            }
        }
    }

    /**
     * Recursively enable options in a requires chain.
     *
     * @param  string                                                                                  $capabilitiesKey
     * @param  \MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesResponseOptionsOptionsV2  $options
     * @param  array<string, OrderOptionDefinitionInterface>                                            $definitionsByCapKey
     * @param  string[]                                                                                $visited Prevents infinite loops on circular requires
     *
     * @return void
     */
    private function applyRequiresChain(
        string $capabilitiesKey,
        RefCapabilitiesResponseOptionsOptionsV2 $options,
        array $definitionsByCapKey,
        array $visited
    ): void {
        $optionValue = $this->getCapabilityOption($options, $capabilitiesKey);

        if (! $optionValue) {
            return;
        }

        foreach ($optionValue->getRequires() ?? [] as $requiredCapKey) {
            if (in_array($requiredCapKey, $visited, true)) {
                continue;
            }

            $requiredDef = $definitionsByCapKey[$requiredCapKey] ?? null;

            if (! $requiredDef || ! $requiredDef->getShipmentOptionsKey()) {
                continue;
            }

            $this->forceOption($requiredDef->getShipmentOptionsKey(), TriStateService::ENABLED);

            $this->applyRequiresChain($requiredCapKey, $options, $definitionsByCapKey, array_merge($visited, [$requiredCapKey]));
        }
    }

    /**
     * @param  \MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesResponseOptionsOptionsV2 $options
     * @param  string                                                                                  $capabilitiesKey
     *
     * @return mixed
     */
    private function getCapabilityOption(RefCapabilitiesResponseOptionsOptionsV2 $options, string $capabilitiesKey)
    {
        $getter = 'get' . ucfirst($capabilitiesKey);

        if (! method_exists($options, $getter)) {
            return null;
        }

        return $options->{$getter}();
    }

    /**
     * @param  string $shipmentKey
     * @param  int    $value
     *
     * @return void
     */
    private function forceOption(string $shipmentKey, int $value): void
    {
        $this->order->deliveryOptions->shipmentOptions->setAttribute($shipmentKey, $value);
    }

    /**
     * @param  \MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface[] $definitions
     *
     * @return array<string, OrderOptionDefinitionInterface>
     */
    private function indexDefinitionsByCapabilitiesKey(array $definitions): array
    {
        $indexed = [];

        foreach ($definitions as $definition) {
            $capKey = $definition->getCapabilitiesOptionsKey();

            if ($capKey) {
                $indexed[$capKey] = $definition;
            }
        }

        return $indexed;
    }
}
