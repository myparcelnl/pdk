<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\General;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\Carrier\Service\CapabilitiesValidationService;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Types\Service\TriStateService;

/**
 * Gives the order an Incoterms rule that the capabilities allow for its carrier and destination.
 * The resolved rule stays when the capabilities allow it. Otherwise the order gets the default rule
 * from the capabilities, or no rule when the capabilities return no incoterms.
 */
final class IncotermsCalculator extends AbstractPdkOrderOptionCalculator
{
    public function calculate(): void
    {
        $shipmentOptions = $this->order->deliveryOptions->shipmentOptions;
        $capability      = $this->fetchShipmentCapability();
        $incoterms       = $capability ? $capability->getIncoterms() : null;
        $rule            = $incoterms ? $incoterms->getRule() : null;

        // DISABLED is "no rule"; INHERIT means "follow the carrier setting" and is not a result.
        if (! $capability || ! $rule) {
            $shipmentOptions->incoterms = TriStateService::DISABLED;

            return;
        }

        /** @var \MyParcelNL\Pdk\Carrier\Service\CapabilitiesValidationService $capabilitiesService */
        $capabilitiesService = Pdk::get(CapabilitiesValidationService::class);

        if (! $capabilitiesService->supportsIncotermsRule($capability, $shipmentOptions->incoterms)) {
            // The SDK declares the default as a ShipmentDefsIncotermsRule model, but at runtime it is a string.
            /** @var string $default */
            $default = $rule->getDefault();

            $shipmentOptions->incoterms = $default;
        }
    }
}
