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
    public function calculate(): void
    {
        $shipmentOptions = $this->order->deliveryOptions->shipmentOptions;

        if ($this->order->shippingAddress->cc !== CountryCodes::CC_NL) {
            $shipmentOptions->receiptCode = TriStateService::DISABLED;
            return;
        }

        if (TriStateService::ENABLED === $shipmentOptions->ageCheck) {
            $shipmentOptions->receiptCode = TriStateService::DISABLED;
            return;
        }

        if (TriStateService::ENABLED !== $shipmentOptions->receiptCode) {
            return;
        }

        $shipmentOptions->signature     = TriStateService::DISABLED;
        $shipmentOptions->onlyRecipient = TriStateService::DISABLED;
        $shipmentOptions->largeFormat   = TriStateService::DISABLED;
        $shipmentOptions->return        = TriStateService::DISABLED;

        if ($shipmentOptions->insurance <= 1) {
            /** @var \MyParcelNL\Pdk\Validation\Validator\CarrierSchema $schema */
            $schema = Pdk::get(CarrierSchema::class);
            $allowedInsuranceAmounts = $schema
                ->setCarrier($this->order->deliveryOptions->carrier)
                ->getAllowedInsuranceAmounts();

            $shipmentOptions->insurance = $this->getMinimumInsuranceAmount($allowedInsuranceAmounts, 10000);
        }
    }

    /**
     * @param  int[] $insuranceAmounts
     * @param  int   $orderAmount
     *
     * @return int
     */
    private function getMinimumInsuranceAmount(array $insuranceAmounts, int $orderAmount): int
    {
        if (empty($insuranceAmounts)) {
            return 0;
        }

        sort($insuranceAmounts);

        if (in_array($orderAmount, $insuranceAmounts)) {
            return $orderAmount;
        }

        if ($orderAmount > end($insuranceAmounts)) {
            return end($insuranceAmounts);
        }

        foreach ($insuranceAmounts as $amount) {
            if ($amount > $orderAmount) {
                return $amount;
            }
        }

        return end($insuranceAmounts);
    }
}
