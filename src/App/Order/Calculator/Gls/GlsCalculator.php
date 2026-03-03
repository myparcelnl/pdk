<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\Gls;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractCarrierOptionsCalculator;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefTypesCarrierV2;

final class GlsCalculator extends AbstractCarrierOptionsCalculator
{
    protected function getCalculators(): array
    {
        return [
            GlsShipmentOptionsCalculator::class,
            GlsInsuranceCalculator::class,
            GlsWeightCalculator::class,
        ];
    }

    protected function getCarrier(): string
    {
        return RefTypesCarrierV2::GLS;
    }
}
