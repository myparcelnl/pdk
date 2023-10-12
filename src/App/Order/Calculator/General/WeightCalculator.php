<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\General;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Types\Service\TriStateService;

final class WeightCalculator extends AbstractPdkOrderOptionCalculator
{
    public function calculate(): void
    {
        $physicalProperties = $this->order->physicalProperties;

        $weight = $physicalProperties->totalWeight + $this->getEmptyWeight();

        $physicalProperties->manualWeight  = TriStateService::INHERIT;
        $physicalProperties->initialWeight = $weight;

        if (! $this->order->customsDeclaration) {
            return;
        }

        $this->order->customsDeclaration->weight = $weight;
    }

    /**
     * Get the empty weight for the package type. Digital stamp is excluded, because its weight has been applied already
     * in the selected range through $order->manualWeight.
     *
     * @return int
     */
    private function getEmptyWeight(): int
    {
        switch ($this->order->deliveryOptions->packageType) {
            case DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME:
                return (int) Settings::get(OrderSettings::EMPTY_PARCEL_WEIGHT, OrderSettings::ID);

            case DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME:
                return (int) Settings::get(OrderSettings::EMPTY_MAILBOX_WEIGHT, OrderSettings::ID);

            default:
                return 0;
        }
    }
}
