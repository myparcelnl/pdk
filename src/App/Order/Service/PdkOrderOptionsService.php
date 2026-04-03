<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Service;

use MyParcelNL\Pdk\App\Options\Contract\OptionDefinitionHelperInterface;
use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\App\Options\Helper\CapabilitiesDefaultHelper;
use MyParcelNL\Pdk\App\Options\Helper\CarrierSettingsDefinitionHelper;
use MyParcelNL\Pdk\App\Options\Helper\ProductSettingsDefinitionHelper;
use MyParcelNL\Pdk\App\Options\Helper\ShipmentOptionsDefinitionHelper;
use MyParcelNL\Pdk\App\Order\Calculator\PdkOrderCalculator;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Types\Contract\TriStateServiceInterface;
use MyParcelNL\Pdk\Types\Service\TriStateService;

class PdkOrderOptionsService implements PdkOrderOptionsServiceInterface
{
    /**
     * @var \MyParcelNL\Pdk\Types\Contract\TriStateServiceInterface
     */
    private $triStateService;

    /**
     * @param  \MyParcelNL\Pdk\Types\Contract\TriStateServiceInterface $triStateService
     */
    public function __construct(TriStateServiceInterface $triStateService)
    {
        $this->triStateService = $triStateService;
    }

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrder $order
     *
     * @return \MyParcelNL\Pdk\App\Order\Model\PdkOrder
     */
    public function calculate(PdkOrder $order): PdkOrder
    {
        return (new PdkOrderCalculator($order))->calculateAll();
    }

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrder $order
     * @param  int                                      $flags
     *
     * @return \MyParcelNL\Pdk\App\Order\Model\PdkOrder
     */
    public function calculateShipmentOptions(PdkOrder $order, int $flags = 0): PdkOrder
    {
        $helpers = Arr::flatten([
            $flags & self::EXCLUDE_SHIPMENT_OPTIONS ? [] : [new ShipmentOptionsDefinitionHelper($order)],
            $flags & self::EXCLUDE_PRODUCT_SETTINGS ? [] : [new ProductSettingsDefinitionHelper($order)],
            $flags & self::EXCLUDE_CARRIER_SETTINGS ? [] : [new CarrierSettingsDefinitionHelper($order)],
            // Capabilities default is always included — lowest priority fallback
            new CapabilitiesDefaultHelper($order),
        ]);

        $carrier = $order->deliveryOptions->carrier;

        /** @var OrderOptionDefinitionInterface[] $definitions */
        $definitions = Pdk::get('orderOptionDefinitions');

        foreach ($definitions as $definition) {
            $values = array_map(static function (OptionDefinitionHelperInterface $helper) use ($definition) {
                return $helper->get($definition);
            }, $helpers);

            $value = $this->triStateService->resolve(...$values);

            // Enforce isRequired: if the carrier capability requires this option, force ENABLED
            $capabilitiesKey = $definition->getCapabilitiesOptionsKey();

            if ($capabilitiesKey && $carrier) {
                $option = $carrier->getOptionMetadata($capabilitiesKey);

                if ($option && $option->getIsRequired()) {
                    $value = TriStateService::ENABLED;
                }
            }

            $order->deliveryOptions->shipmentOptions->setAttribute($definition->getShipmentOptionsKey(), $value);
        }

        return $order;
    }
}
