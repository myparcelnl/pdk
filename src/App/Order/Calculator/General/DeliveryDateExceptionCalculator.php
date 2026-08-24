<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\General;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\Types\Service\TriStateService;
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

            return;
        }

        /**
         * The API rejects a delivery date combined with collect, but it does not publish that
         * constraint in the capabilities options. There is no option key for the delivery date,
         * so an excludes entry cannot reach it and {@see CapabilitiesOptionCalculator} cannot
         * apply the rule. Drop the date here until the capabilities response expresses it.
         *
         * @TODO: remove once the delivery date is part of the capabilities options
         */
        if (TriStateService::ENABLED === ($this->order->deliveryOptions->shipmentOptions->collect ?? null)) {
            $this->order->deliveryOptions->date = null;
        }
    }
}
