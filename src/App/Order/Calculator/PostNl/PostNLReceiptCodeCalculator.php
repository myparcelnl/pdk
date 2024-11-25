<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\PostNl;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use MyParcelNL\Pdk\Validation\Validator\CarrierSchema;
use MyParcelNL\Pdk\Facade\Pdk;

/**
 * When receipt code is enabled, insurance is required.
 */
final class PostNLReceiptCodeCalculator extends AbstractPdkOrderOptionCalculator
{
    /**
     * Calculates the receipt code options for PostNL shipments.
     * When receipt code is enabled:
     * - Shipment must be to the Netherlands
     * - Receipt code will be disabled if age check is active
     * - Signature and only recipient will be disabled
     * - Large format will be disabled
     * - Return will be disabled
     * - Insurance will be enabled (minimum €100) when no insurance is active
     */
    public function calculate(): void
    {
        $shipmentOptions = $this->order->deliveryOptions->shipmentOptions;

        if (TriStateService::ENABLED !== $shipmentOptions->receiptCode) {
            return;
        }

        if (! in_array($this->order->shippingAddress->cc, [CountryCodes::CC_NL, CountryCodes::CC_BE])) {
            $shipmentOptions->receiptCode = TriStateService::DISABLED;
            return;
        }

        if (TriStateService::ENABLED === $shipmentOptions->ageCheck) {
            $shipmentOptions->receiptCode = TriStateService::DISABLED;
            return;
        }

        $shipmentOptions->signature     = TriStateService::DISABLED;
        $shipmentOptions->onlyRecipient = TriStateService::DISABLED;
        $shipmentOptions->largeFormat   = TriStateService::DISABLED;
        $shipmentOptions->return        = TriStateService::DISABLED;

        if ($shipmentOptions->insurance === TriStateService::DISABLED) {
            /** @var \MyParcelNL\Pdk\Validation\Validator\CarrierSchema $schema */
            $schema                  = Pdk::get(CarrierSchema::class);
            $allowedInsuranceAmounts = $schema
                ->setCarrier($this->order->deliveryOptions->carrier)
                ->getAllowedInsuranceAmounts();

            $shipmentOptions->insurance = $this->getLowestInsuranceAmount($allowedInsuranceAmounts);
        }
    }

    /**
     * Gets the lowest allowed insurance amount that is greater than 0.
     *
     * @param  int[] $insuranceAmount
     *
     * @return int
     */
    private function getLowestInsuranceAmount(array $insuranceAmount): int
    {
        $lowestAmount = null;

        foreach ($insuranceAmount as $allowedInsuranceAmount) {
            if ($allowedInsuranceAmount <= 0) {
                continue;
            }

            if (null === $lowestAmount || $allowedInsuranceAmount < $lowestAmount) {
                $lowestAmount = $allowedInsuranceAmount;
            }
        }

        return $lowestAmount ?? 0;
    }
}
