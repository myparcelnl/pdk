<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Options\Helper;

use MyParcelNL\Pdk\App\Options\Contract\OptionDefinitionHelperInterface;
use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Types\Service\TriStateService;

/**
 * Provides isSelectedByDefault from carrier capabilities as the lowest-priority default.
 *
 * Sits at the end of the resolution chain so it only takes effect when all other
 * sources (shipment options, product settings, carrier settings) are INHERIT.
 */
final class CapabilitiesDefaultHelper implements OptionDefinitionHelperInterface
{
    /**
     * @var \MyParcelNL\Pdk\App\Order\Model\PdkOrder
     */
    private $order;

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrder $order
     */
    public function __construct(PdkOrder $order)
    {
        $this->order = $order;
    }

    /**
     * @param  \MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface $definition
     *
     * @return int
     */
    public function get(OrderOptionDefinitionInterface $definition)
    {
        $capabilitiesKey = $definition->getCapabilitiesOptionsKey();

        if (! $capabilitiesKey) {
            return TriStateService::INHERIT;
        }

        $carrier = $this->order->deliveryOptions->carrier;

        $option = $carrier->getOptionMetadata($capabilitiesKey);

        if ($option && ($option->getIsRequired() || $option->getIsSelectedByDefault())) {
            return TriStateService::ENABLED;
        }

        return TriStateService::INHERIT;
    }
}
