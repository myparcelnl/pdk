<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\General;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;

final class WeightCalculator extends AbstractPdkOrderOptionCalculator
{
    public function calculate(): void
    {
        $emptyWeight = $this->getEmptyWeight();

        $this->order->physicalProperties->weight += $emptyWeight;

        if (! $this->order->customsDeclaration) {
            return;
        }

        $this->order->customsDeclaration->weight += $emptyWeight;
    }

    private function getEmptyWeight(): int
    {
        switch ($this->order->deliveryOptions->packageType) {
            case DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME:
                return (int) Settings::get(OrderSettings::EMPTY_PARCEL_WEIGHT, OrderSettings::ID);

            case DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME:
                return (int) Settings::get(OrderSettings::EMPTY_MAILBOX_WEIGHT, OrderSettings::ID);

            case DeliveryOptions::PACKAGE_TYPE_DIGITAL_STAMP_NAME:
                return (int) Settings::get(OrderSettings::EMPTY_DIGITAL_STAMP_WEIGHT, OrderSettings::ID);

            default:
                return 0;
        }
    }
}
