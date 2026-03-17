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
use MyParcelNL\Pdk\App\Order\Calculator\Trunkrs\TrunkrsCalculator;
use MyParcelNL\Pdk\App\Order\Calculator\UPSStandard\UPSStandardCalculator;
use MyParcelNL\Pdk\App\Order\Calculator\UPSExpressSaver\UPSExpressSaverCalculator;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionCalculatorInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Proposition\Service\PropositionService;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2;

final class CarrierSpecificCalculator extends AbstractPdkOrderOptionCalculator
{
    // ...existing code...
    /**
     * @var array<string, class-string<PdkOrderOptionCalculatorInterface>>
     */
    private const CARRIER_CALCULATOR_MAP = [
        RefCapabilitiesSharedCarrierV2::POSTNL             => PostNLCalculator::class,
        RefCapabilitiesSharedCarrierV2::DHL_FOR_YOU        => DhlForYouCalculator::class,
        RefCapabilitiesSharedCarrierV2::DHL_EUROPLUS       => DhlEuroplusCalculator::class,
        RefCapabilitiesSharedCarrierV2::DHL_PARCEL_CONNECT => DhlParcelConnectCalculator::class,
        RefCapabilitiesSharedCarrierV2::DPD                => DpdCalculator::class,
        RefCapabilitiesSharedCarrierV2::UPS_STANDARD       => UPSStandardCalculator::class,
        RefCapabilitiesSharedCarrierV2::UPS_EXPRESS_SAVER  => UPSExpressSaverCalculator::class,
        RefCapabilitiesSharedCarrierV2::BPOST              => BpostCalculator::class,
        RefCapabilitiesSharedCarrierV2::GLS                => GlsCalculator::class,
        RefCapabilitiesSharedCarrierV2::TRUNKRS            => TrunkrsCalculator::class,
    ];

    public function calculate(): void
    {
        $carrierName = $this->order->deliveryOptions->carrier->carrier;
        $calculator  = self::CARRIER_CALCULATOR_MAP[$carrierName] ?? null;

        if (! $calculator) {
            return;
        }

        /** @var PdkOrderOptionCalculatorInterface $calculator */
        $calculator = new $calculator($this->order);

        $calculator->calculate();
    }
}
