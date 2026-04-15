<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\General;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2;

final class DeliveryDateExceptionCalculator extends AbstractPdkOrderOptionCalculator
{
    // @TODO: deliveryDate is not part of capabilities but considered validation metadata — needs a different API endpoint
    private const CARRIERS_WITHOUT_DELIVERY_DATE = [
        RefCapabilitiesSharedCarrierV2::BPOST,
        RefCapabilitiesSharedCarrierV2::DPD,
    ];

    public function calculate(): void
    {
        $carrierName = $this->order->deliveryOptions->carrier->carrier ?? null;

        if (in_array($carrierName, self::CARRIERS_WITHOUT_DELIVERY_DATE, true)) {
            $this->order->deliveryOptions->date = null;
        }
    }
}
