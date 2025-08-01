<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\Gls;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;

/**
 * Set minimum weight to 100 grams for GLS shipments as per GLS requirements
 */
final class GlsWeightCalculator extends AbstractPdkOrderOptionCalculator
{
    private const MINIMUM_WEIGHT = 100;

    public function calculate(): void
    {
        $physicalProperties = $this->order->physicalProperties;

        // Ensure minimum weight of 100 grams for GLS
        if ($physicalProperties->initialWeight < self::MINIMUM_WEIGHT) {
            $physicalProperties->initialWeight = self::MINIMUM_WEIGHT;
        }
    }
}
