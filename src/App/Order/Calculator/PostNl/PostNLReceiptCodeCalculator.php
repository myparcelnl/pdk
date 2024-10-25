<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\PostNl;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\Types\Service\TriStateService;

/**
 * When receipt code is enabled, insurance is required.
 */
final class PostNLReceiptCodeCalculator extends AbstractPdkOrderOptionCalculator
{
    public function calculate(): void
    {
        $shipmentOptions = $this->order->deliveryOptions->shipmentOptions;

        if ($this->order->shippingAddress->cc !== 'NL') {
            $shipmentOptions->receiptCode = TriStateService::DISABLED;
            return;
        }

        if (TriStateService::ENABLED === $shipmentOptions->ageCheck) {
            $shipmentOptions->receiptCode = TriStateService::DISABLED;
            return;
        }

        if (TriStateService::ENABLED !== $shipmentOptions->receiptCode) {
            return;
        }

        $shipmentOptions->signature     = TriStateService::DISABLED;
        $shipmentOptions->onlyRecipient = TriStateService::DISABLED;
        $shipmentOptions->largeFormat   = TriStateService::DISABLED;
        $shipmentOptions->return        = TriStateService::DISABLED;

        if ($shipmentOptions->insurance <= TriStateService::ENABLED) {
            $shipmentOptions->insurance = 10000;
        }
    }
}
