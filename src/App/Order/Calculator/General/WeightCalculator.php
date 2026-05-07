<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\General;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\Base\Contract\WeightServiceInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Model\PackageType;
use MyParcelNL\Pdk\Types\Service\TriStateService;

final class WeightCalculator extends AbstractPdkOrderOptionCalculator
{
    public function calculate(): void
    {
        $physicalProperties = $this->order->physicalProperties;

        $weight = Pdk::get(WeightServiceInterface::class)->getEffectiveWeight(
            $physicalProperties,
            new PackageType(['name' => $this->order->deliveryOptions->packageType])
        );

        $physicalProperties->manualWeight  = TriStateService::INHERIT;
        $physicalProperties->initialWeight = $weight;
    }
}
