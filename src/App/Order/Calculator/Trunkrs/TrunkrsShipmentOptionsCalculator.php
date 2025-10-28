<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\Trunkrs;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Types\Service\TriStateService;

final class TrunkrsShipmentOptionsCalculator extends AbstractPdkOrderOptionCalculator
{
    public function __construct(PdkOrder $order)
    {
        parent::__construct($order);
    }

    public function calculate(): void
    {
        $options = $this->order->deliveryOptions->shipmentOptions;

        // AgeCheck requires Signature and OnlyRecipient
        if (TriStateService::ENABLED === $options->ageCheck) {
            $options->signature     = TriStateService::ENABLED;
            $options->onlyRecipient = TriStateService::ENABLED;
        }
    }
}


