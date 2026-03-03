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
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefTypesCarrierV2;
use MyParcelNL\Pdk\Proposition\Service\PropositionService;

final class CarrierSpecificCalculator extends AbstractPdkOrderOptionCalculator
{
    // ...existing code...
    /**
     * @var array<string, class-string<PdkOrderOptionCalculatorInterface>>
     */
    private const CARRIER_CALCULATOR_MAP = [
        RefTypesCarrierV2::POSTNL             => PostNLCalculator::class,
        RefTypesCarrierV2::DHL_FOR_YOU        => DhlForYouCalculator::class,
        RefTypesCarrierV2::DHL_EUROPLUS       => DhlEuroplusCalculator::class,
        RefTypesCarrierV2::DHL_PARCEL_CONNECT => DhlParcelConnectCalculator::class,
        RefTypesCarrierV2::DPD                => DpdCalculator::class,
        RefTypesCarrierV2::UPS_STANDARD       => UPSStandardCalculator::class,
        RefTypesCarrierV2::UPS_EXPRESS_SAVER  => UPSExpressSaverCalculator::class,
        RefTypesCarrierV2::BPOST              => BpostCalculator::class,
        RefTypesCarrierV2::GLS                => GlsCalculator::class,
        RefTypesCarrierV2::TRUNKRS            => TrunkrsCalculator::class,
    ];

    public function calculate(): void
    {
        $carrierName = Pdk::get(PropositionService::class)->mapLegacyToNewCarrierName($this->order->deliveryOptions->carrier->name);
        $calculator  = self::CARRIER_CALCULATOR_MAP[$carrierName] ?? null;

        if (! $calculator) {
            return;
        }

        /** @var PdkOrderOptionCalculatorInterface $calculator */
        $calculator = new $calculator($this->order);

        $calculator->calculate();
    }
}
