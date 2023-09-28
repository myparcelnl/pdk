<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\DhlParcelConnect;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\Types\Service\TriStateService;

/**
 * - Signature is always enabled
 */
final class DhlParcelConnectShipmentOptionsCalculator extends AbstractPdkOrderOptionCalculator
{
    public function calculate(): void
    {
        $shipmentOptions = $this->order->deliveryOptions->shipmentOptions;

        $shipmentOptions->signature = TriStateService::ENABLED;
    }
}
