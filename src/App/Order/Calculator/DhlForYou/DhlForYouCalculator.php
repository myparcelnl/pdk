<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\DhlForYou;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractCarrierOptionsCalculator;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefTypesCarrierV2;

final class DhlForYouCalculator extends AbstractCarrierOptionsCalculator
{
    protected function getCalculators(): array
    {
        return [
            DhlForYouDeliveryTypeCalculator::class,
            DhlForYouShipmentOptionsCalculator::class,
        ];
    }

    protected function getCarrier(): string
    {
        return RefTypesCarrierV2::DHL_FOR_YOU;
    }
}
