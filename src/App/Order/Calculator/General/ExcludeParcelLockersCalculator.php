<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\General;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;
use MyParcelNL\Pdk\Types\Service\TriStateService;

/**
 * Automatically exclude parcel lockers for 18+ products and when general setting is enabled.
 */
final class ExcludeParcelLockersCalculator extends AbstractPdkOrderOptionCalculator
{
    public function calculate(): void
    {
        $shipmentOptions = $this->order->deliveryOptions->shipmentOptions;

        // Check if parcel lockers should be excluded based on general setting
        $generalExcludeParcelLockers = Settings::get(
            CheckoutSettings::EXCLUDE_PARCEL_LOCKERS,
            CheckoutSettings::ID,
            false
        );

        // Check if any product in the order has 18+ classification
        $has18PlusProduct = $this->order->lines->contains(function ($orderLine) {
            $productSettings = $orderLine->product->mergedSettings;
            return TriStateService::ENABLED === $productSettings->exportAgeCheck;
        });

        // Check if any product explicitly excludes parcel lockers
        $productExcludesParcelLockers = $this->order->lines->contains(function ($orderLine) {
            $productSettings = $orderLine->product->mergedSettings;
            return TriStateService::ENABLED === $productSettings->excludeParcelLockers;
        });

        // Set excludeParcelLockers to ENABLED if any condition is met
        if ($generalExcludeParcelLockers || $has18PlusProduct || $productExcludesParcelLockers) {
            $shipmentOptions->excludeParcelLockers = TriStateService::ENABLED;
        }
    }
}
