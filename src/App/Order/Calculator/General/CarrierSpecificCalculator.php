<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\General;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\App\Order\Calculator\Bpost\BpostCalculator;
use MyParcelNL\Pdk\App\Order\Calculator\DhlEuroplus\DhlEuroplusCalculator;
use MyParcelNL\Pdk\App\Order\Calculator\DhlForYou\DhlForYouCalculator;
use MyParcelNL\Pdk\App\Order\Calculator\DhlParcelConnect\DhlParcelConnectCalculator;
use MyParcelNL\Pdk\App\Order\Calculator\Dpd\DpdCalculator;
use MyParcelNL\Pdk\App\Order\Calculator\Gls\GlsCalculator;
use MyParcelNL\Pdk\App\Order\Calculator\PostNl\PostNLCalculator;
use MyParcelNL\Pdk\App\Order\Calculator\UPSStandard\UPSStandardCalculator;
use MyParcelNL\Pdk\App\Order\Calculator\UPSExpressSaver\UPSExpressSaverCalculator;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionCalculatorInterface;
use MyParcelNL\Pdk\Carrier\Model\Carrier;

final class CarrierSpecificCalculator extends AbstractPdkOrderOptionCalculator
{
    // ...existing code...
    /**
     * @var array<string, class-string<PdkOrderOptionCalculatorInterface>>
     */
    private const CARRIER_CALCULATOR_MAP = [
        Carrier::CARRIER_POSTNL_LEGACY_NAME             => PostNLCalculator::class,
        Carrier::CARRIER_DHL_FOR_YOU_LEGACY_NAME        => DhlForYouCalculator::class,
        Carrier::CARRIER_DHL_EUROPLUS_LEGACY_NAME       => DhlEuroplusCalculator::class,
        Carrier::CARRIER_DHL_PARCEL_CONNECT_LEGACY_NAME => DhlParcelConnectCalculator::class,
        Carrier::CARRIER_DPD_LEGACY_NAME                => DpdCalculator::class,
        Carrier::CARRIER_UPS_STANDARD_LEGACY_NAME       => UPSStandardCalculator::class,
        Carrier::CARRIER_UPS_EXPRESS_SAVER_LEGACY_NAME  => UPSExpressSaverCalculator::class,
        Carrier::CARRIER_BPOST_LEGACY_NAME              => BpostCalculator::class,
        Carrier::CARRIER_GLS_LEGACY_NAME                => GlsCalculator::class,
    ];

    public function calculate(): void
    {
        $carrierName = $this->order->deliveryOptions->carrier->name;
        $calculator  = self::CARRIER_CALCULATOR_MAP[$carrierName] ?? null;

        if (! $calculator) {
            return;
        }

        /** @var PdkOrderOptionCalculatorInterface $calculator */
        $calculator = new $calculator($this->order);

        $calculator->calculate();
    }
}
