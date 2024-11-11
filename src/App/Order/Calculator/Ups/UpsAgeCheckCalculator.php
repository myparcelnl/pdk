<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\Ups;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\Types\Service\TriStateService;

/**
 * When age check is enabled, signature and only recipient are required.
 */
final class UpsAgeCheckCalculator extends AbstractPdkOrderOptionCalculator
{
    //todo: increase coverage
    public function calculate(): void
    {
        $shipmentOptions = $this->order->deliveryOptions->shipmentOptions;

        if (TriStateService::ENABLED !== $shipmentOptions->ageCheck) {
            return;
        }

        $shipmentOptions->signature     = TriStateService::ENABLED;
        $shipmentOptions->onlyRecipient = TriStateService::ENABLED;
    }
}
