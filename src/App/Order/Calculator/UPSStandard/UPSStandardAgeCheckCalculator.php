<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\UPSStandard;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\Types\Service\TriStateService;

/**
 * When age check is enabled, signature is required.
 */
final class UPSStandardAgeCheckCalculator extends AbstractPdkOrderOptionCalculator
{
    public function calculate(): void
    {
        $shipmentOptions = $this->order->deliveryOptions->shipmentOptions;

        if (TriStateService::ENABLED !== $shipmentOptions->ageCheck) {
            return;
        }

        $shipmentOptions->signature = TriStateService::ENABLED;
    }
}
