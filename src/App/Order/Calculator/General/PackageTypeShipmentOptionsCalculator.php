<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\General;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Types\Service\TriStateService;

/**
 * Disables all shipment options if package type is not package.
 */
final class PackageTypeShipmentOptionsCalculator extends AbstractPdkOrderOptionCalculator
{
    public function calculate(): void
    {
        $deliveryOptions = $this->order->deliveryOptions;

        if (DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME === $deliveryOptions->packageType) {
            return;
        }

        if (DeliveryOptions::PACKAGE_TYPE_PACKAGE_SMALL_NAME === $deliveryOptions->packageType) {
            $this->order->deliveryOptions->shipmentOptions->tracked = TriStateService::ENABLED;
        } else {
            $this->order->deliveryOptions->shipmentOptions->tracked = TriStateService::DISABLED;
        }

        $this->order->deliveryOptions->shipmentOptions->fill([
            ShipmentOptions::AGE_CHECK         => TriStateService::DISABLED,
            ShipmentOptions::DIRECT_RETURN     => TriStateService::DISABLED,
            ShipmentOptions::HIDE_SENDER       => TriStateService::DISABLED,
            ShipmentOptions::LARGE_FORMAT      => TriStateService::DISABLED,
            ShipmentOptions::ONLY_RECIPIENT    => TriStateService::DISABLED,
            ShipmentOptions::SAME_DAY_DELIVERY => TriStateService::DISABLED,
            ShipmentOptions::SIGNATURE         => TriStateService::DISABLED,
        ]);
    }
}
