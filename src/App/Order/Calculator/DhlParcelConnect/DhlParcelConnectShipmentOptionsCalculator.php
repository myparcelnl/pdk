<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\DhlParcelConnect;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Types\Service\TriStateService;

/**
 * - Evening delivery is only allowed in the Netherlands.
 * - When evening delivery is enabled same-day delivery is not available
 */
final class DhlParcelConnectShipmentOptionsCalculator extends AbstractPdkOrderOptionCalculator
{
    /**
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrder $order
     */
    public function __construct(PdkOrder $order)
    {
        parent::__construct($order);
    }

    public function calculate(): void
    {
        $shipmentOptions = $this->order->deliveryOptions->shipmentOptions;

        $shipmentOptions->signature = TriStateService::ENABLED;
    }
}
