<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Service;

use MyParcelNL\Pdk\App\Options\Contract\OptionDefinitionHelperInterface;
use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\App\Options\Helper\CarrierSettingsDefinitionHelper;
use MyParcelNL\Pdk\App\Options\Helper\ProductSettingsDefinitionHelper;
use MyParcelNL\Pdk\App\Options\Helper\ShipmentOptionsDefinitionHelper;
use MyParcelNL\Pdk\App\Order\Calculator\PdkOrderCalculator;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Types\Contract\TriStateServiceInterface;

class PdkOrderOptionsService implements PdkOrderOptionsServiceInterface
{
    public function __construct(private readonly TriStateServiceInterface $triStateService)
    {
    }

    public function calculate(PdkOrder $order): PdkOrder
    {
        return (new PdkOrderCalculator($order))->calculateAll();
    }

    public function calculateShipmentOptions(PdkOrder $order, int $flags = 0): PdkOrder
    {
        $helpers = Arr::flatten([
            $flags & self::EXCLUDE_SHIPMENT_OPTIONS ? [] : [new ShipmentOptionsDefinitionHelper($order)],
            $flags & self::EXCLUDE_PRODUCT_SETTINGS ? [] : [new ProductSettingsDefinitionHelper($order)],
            $flags & self::EXCLUDE_CARRIER_SETTINGS ? [] : [new CarrierSettingsDefinitionHelper($order)],
        ]);

        /** @var OrderOptionDefinitionInterface[] $definitions */
        $definitions = Pdk::get('orderOptionDefinitions');

        foreach ($definitions as $definition) {
            $values = array_map(static fn(OptionDefinitionHelperInterface $helper) => $helper->get($definition),
                $helpers);

            $value = $this->triStateService->resolve(...$values);

            $order->deliveryOptions->shipmentOptions->setAttribute($definition->getShipmentOptionsKey(), $value);
        }

        return $order;
    }
}
