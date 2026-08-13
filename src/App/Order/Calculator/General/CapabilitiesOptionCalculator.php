<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\General;

use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Carrier\Service\CapabilitiesValidationService;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
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
     * Options read from the capabilities response, keyed by capability key. Filled while one
     * calculation runs.
     *
     * @var array<string, mixed>
     */
    private $optionValues = [];

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
        $this->optionValues = [];

        $capability = $this->getCarrierCapabilities();

        /** @var OrderOptionDefinitionInterface[] $definitions */
        $definitions = Pdk::get('orderOptionDefinitions');

        // No capability for this carrier+package_type+delivery_type combination —
        // none of the shipment options are supported, drop them all.
        if (! $capability) {
            $this->disableAllOptions($definitions);

            return;
        }

        $this->setContractId($capability);

        $options = $capability->getOptions();

        // Index definitions by capabilities key for requires/excludes lookups.
        $definitionsByCapKey = $this->indexDefinitionsByCapabilitiesKey($definitions);

        // First pass: apply per-option capabilities constraints (isRequired, presence in response).
        foreach ($definitions as $definition) {
            $this->applyDefinition($definition, $options);
        }

        // Second pass: propagate requires/excludes for enabled options.
        $this->propagateConstraints($definitions, $options, $definitionsByCapKey);
    }

    /**
     * Disable every shipment option that has a definition. Used when the carrier
     * has no capability for the current shipment context — nothing is supported.
     *
     * @param  \MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface[] $definitions
     */
    private function disableAllOptions(array $definitions): void
    {
        foreach ($definitions as $definition) {
            $shipmentKey = $definition->getShipmentOptionsKey();

            if ($shipmentKey) {
                $this->forceOption($shipmentKey, TriStateService::DISABLED);
            }
        }
    }

    /**
     * @return null|\MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesResponseCapabilityV2
     */
    private function getCarrierCapabilities(): ?RefCapabilitiesResponseCapabilityV2
    {
        $deliveryOptions = $this->order->deliveryOptions;
        $carrierName     = $deliveryOptions->carrier->carrier;
        $cc              = $this->order->shippingAddress->cc;
        $v2PackageType   = DeliveryOptions::PACKAGE_TYPES_V2_MAP[$deliveryOptions->packageType] ?? null;
        $v2DeliveryType  = DeliveryOptions::DELIVERY_TYPES_V2_MAP[$deliveryOptions->deliveryType] ?? null;

        if (! $cc || ! $v2PackageType) {
            return null;
        }

        $args = [
            'carrier'      => $carrierName,
            'recipient'    => $this->capabilitiesRecipient(),
            'package_type' => $v2PackageType,
        ];

        if ($v2DeliveryType) {
            $args['delivery_type'] = $v2DeliveryType;
        }

        $capabilities = $this->capabilitiesService->indexByCarrier(
            $this->capabilitiesService->getRepository()->getCapabilities($args)
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
     * Apply capabilities constraints for a single definition.
     *
     * Merchant `allow*` flags are intentionally NOT consulted here — they're a
     * checkout-display concern (filtered by {@see DeliveryOptionsService}). At order
     * processing time capabilities have final say: forcing an option DISABLED because
     * the merchant disallowed it would produce orders the API rejects when capabilities
     * say the option is required for the chosen shipment context.
     *
     * @param  \MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface                    $definition
     * @param  \MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesResponseOptionsOptionsV2 $options
     *
     * @return void
     */
    private function applyDefinition(
        OrderOptionDefinitionInterface $definition,
        RefCapabilitiesResponseOptionsOptionsV2 $options
    ): void {
        $shipmentKey     = $definition->getShipmentOptionsKey();
        $capabilitiesKey = $definition->getCapabilitiesOptionsKey();

        if (! $shipmentKey || ! $capabilitiesKey) {
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
     * Apply the requires and excludes rules of the enabled options. If two rules disagree, the
     * first rule has effect. The calculator writes a warning about the other rule.
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
        $activeKeys   = $this->collectEnabledCapabilitiesKeys($definitions);
        $requiredKeys = $this->collectRequiredCapabilitiesKeys($definitionsByCapKey, $options);

        // The capabilities data requires these options. These options are on and locked.
        $forcedOn  = array_fill_keys($requiredKeys, true);
        $forcedOff = [];

        // The rules come from two groups of options: the group that capabilities require, and the
        // group that is enabled on the order. An option can be in both groups, and thus this
        // removes the duplicates. An enabled option is a source of rules only. It does not force
        // its own value, and the merchant keeps control of that value.
        $ruleSources = array_unique(array_merge($requiredKeys, $activeKeys));

        // The queue holds the options that the loop must still read. The visited list prevents an
        // endless loop if two options require each other.
        $queue   = $ruleSources;
        $visited = array_fill_keys($ruleSources, true);

        while ($queue) {
            $capabilitiesKey = array_shift($queue);
            $optionValue     = $this->getCapabilityOption($options, $capabilitiesKey);

            if (! $optionValue) {
                continue;
            }

            foreach ($optionValue->getRequires() ?? [] as $requiredCapKey) {
                if (isset($forcedOff[$requiredCapKey])) {
                    $this->logConflict($requiredCapKey, $capabilitiesKey, 'require', 'excluded');

                    continue;
                }

                $forcedOn[$requiredCapKey] = true;

                if (! isset($visited[$requiredCapKey])) {
                    // Add the option to the end of the queue. The rules of the enabled options
                    // apply first. The rules of an option from a requires chain apply after them.
                    $visited[$requiredCapKey] = true;
                    $queue[]                  = $requiredCapKey;
                }
            }

            foreach ($optionValue->getExcludes() ?? [] as $excludedCapKey) {
                if (isset($forcedOn[$excludedCapKey])) {
                    $this->logConflict($excludedCapKey, $capabilitiesKey, 'exclude', 'required');

                    continue;
                }

                $forcedOff[$excludedCapKey] = true;
            }
        }

        foreach (array_keys($forcedOff) as $capabilitiesKey) {
            $this->forceOptionByCapabilitiesKey($capabilitiesKey, $definitionsByCapKey, TriStateService::DISABLED);
        }

        foreach (array_keys($forcedOn) as $capabilitiesKey) {
            $this->forceOptionByCapabilitiesKey($capabilitiesKey, $definitionsByCapKey, TriStateService::ENABLED);
        }
    }

    /**
     * Write a warning about a rule that the calculator does not apply. The capabilities data has
     * a conflict. Correct the data at the source.
     *
     * @param  string $capabilitiesKey Option the rule points at.
     * @param  string $sourceKey       Option that holds the rule.
     * @param  string $action          `require` or `exclude`.
     * @param  string $state           `required` or `excluded`.
     *
     * @return void
     */
    private function logConflict(string $capabilitiesKey, string $sourceKey, string $action, string $state): void
    {
        Logger::warning(
            sprintf(
                'Can\'t %s "%s", it\'s already %s by another option; capabilities rules contradict each other.',
                $action,
                $capabilitiesKey,
                $state
            ),
            [
                'option' => $capabilitiesKey,
                'source' => $sourceKey,
                'action' => $action,
                'state'  => $state,
            ]
        );
    }

    /**
     * Get the capability keys of the options that the capabilities data requires.
     *
     * @param  array<string, OrderOptionDefinitionInterface>                                           $definitionsByCapKey
     * @param  \MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesResponseOptionsOptionsV2 $options
     *
     * @return string[]
     */
    private function collectRequiredCapabilitiesKeys(
        array $definitionsByCapKey,
        RefCapabilitiesResponseOptionsOptionsV2 $options
    ): array {
        $keys = [];

        foreach (array_keys($definitionsByCapKey) as $capabilitiesKey) {
            $optionValue = $this->getCapabilityOption($options, $capabilitiesKey);

            if ($optionValue && $optionValue->getIsRequired()) {
                $keys[] = $capabilitiesKey;
            }
        }

        return $keys;
    }

    /**
     * Get the capability keys of the options that are enabled on the order.
     *
     * @param  \MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface[] $definitions
     *
     * @return string[]
     */
    private function collectEnabledCapabilitiesKeys(array $definitions): array
    {
        $keys = [];

        foreach ($definitions as $definition) {
            $shipmentKey     = $definition->getShipmentOptionsKey();
            $capabilitiesKey = $definition->getCapabilitiesOptionsKey();

            if (! $shipmentKey || ! $capabilitiesKey) {
                continue;
            }

            $currentValue = $this->order->deliveryOptions->shipmentOptions->getAttribute($shipmentKey);

            if ($currentValue === TriStateService::ENABLED) {
                $keys[] = $capabilitiesKey;
            }
        }

        return $keys;
    }

    /**
     * Force the option a capability key belongs to, when a definition maps it to a shipment option.
     *
     * @param  string                                        $capabilitiesKey
     * @param  array<string, OrderOptionDefinitionInterface> $definitionsByCapKey
     * @param  int                                            $value
     *
     * @return void
     */
    private function forceOptionByCapabilitiesKey(
        string $capabilitiesKey,
        array $definitionsByCapKey,
        int $value
    ): void {
        $definition  = $definitionsByCapKey[$capabilitiesKey] ?? null;
        $shipmentKey = $definition ? $definition->getShipmentOptionsKey() : null;

        if ($shipmentKey) {
            $this->forceOption($shipmentKey, $value);
        }
    }

    /**
     * Read one option from the capabilities response. The result of each key is kept, and thus a
     * key that has no getter gives one warning only.
     *
     * @param  \MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesResponseOptionsOptionsV2 $options
     * @param  string                                                                                  $capabilitiesKey
     *
     * @return mixed
     */
    private function getCapabilityOption(RefCapabilitiesResponseOptionsOptionsV2 $options, string $capabilitiesKey)
    {
        if (array_key_exists($capabilitiesKey, $this->optionValues)) {
            return $this->optionValues[$capabilitiesKey];
        }

        return $this->optionValues[$capabilitiesKey] = $this->readCapabilityOption($options, $capabilitiesKey);
    }

    /**
     * @param  \MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesResponseOptionsOptionsV2 $options
     * @param  string                                                                                  $capabilitiesKey
     *
     * @return mixed
     */
    private function readCapabilityOption(RefCapabilitiesResponseOptionsOptionsV2 $options, string $capabilitiesKey)
    {
        $getter = 'get' . ucfirst($capabilitiesKey);

        if (! method_exists($options, $getter)) {
            Logger::warning(
                sprintf(
                    'No getter %s() on %s for capabilities key "%s"; check the OptionDefinition\'s getCapabilitiesOptionsKey().',
                    $getter,
                    RefCapabilitiesResponseOptionsOptionsV2::class,
                    $capabilitiesKey
                ),
                [
                    'capabilitiesKey' => $capabilitiesKey,
                    'expectedGetter'  => $getter,
                    'optionsClass'    => RefCapabilitiesResponseOptionsOptionsV2::class,
                ]
            );

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
