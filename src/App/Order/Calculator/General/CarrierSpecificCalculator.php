<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\General;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\App\Order\Calculator\DhlForYou\DhlForYouCalculator;
use MyParcelNL\Pdk\App\Order\Calculator\PostNl\PostNLCalculator;
use MyParcelNL\Pdk\Carrier\Model\Carrier;

final class CarrierSpecificCalculator extends AbstractPdkOrderOptionCalculator
{
    /**
     * @var array<string, class-string<\MyParcelNL\Pdk\App\Cart\Contract\CartCalculationServiceInterface>>
     */
    private const CARRIER_CALCULATOR_MAP = [
        Carrier::CARRIER_POSTNL_NAME      => PostNLCalculator::class,
        Carrier::CARRIER_DHL_FOR_YOU_NAME => DhlForYouCalculator::class,
    ];

    public function calculate(): void
    {
        $carrierName = $this->order->deliveryOptions->carrier->name;
        $calculator  = self::CARRIER_CALCULATOR_MAP[$carrierName] ?? null;

        if (! $calculator) {
            return;
        }

        /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionCalculatorInterface $calculator */
        $calculator = new $calculator($this->order);

        $calculator->calculate();
    }
}
