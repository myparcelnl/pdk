<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\General;

use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Types\Service\TriStateService;

/**
 * Disables all shipment options if package type is not package.
 * Iterates registered definitions so new options are automatically included.
 */
final class PackageTypeShipmentOptionsCalculator extends AbstractPdkOrderOptionCalculator
{
    public function calculate(): void
    {
        $deliveryOptions = $this->order->deliveryOptions;

        if (DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME === $deliveryOptions->packageType) {
            return;
        }

        /** @var OrderOptionDefinitionInterface[] $definitions */
        $definitions = Pdk::get('orderOptionDefinitions');

        $disabled = [];

        foreach ($definitions as $definition) {
            $key = $definition->getShipmentOptionsKey();

            if ($key !== null) {
                $disabled[$key] = TriStateService::DISABLED;
            }
        }

        $this->order->deliveryOptions->shipmentOptions->fill($disabled);
    }
}
