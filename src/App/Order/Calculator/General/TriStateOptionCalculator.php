<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\General;

use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\App\Options\Helper\CarrierSettingsDefinitionHelper;
use MyParcelNL\Pdk\App\Options\Helper\ProductSettingsDefinitionHelper;
use MyParcelNL\Pdk\App\Options\Helper\ShipmentOptionsDefinitionHelper;
use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Types\Contract\TriStateServiceInterface;
use MyParcelNL\Pdk\Validation\Validator\CarrierSchema;

final class TriStateOptionCalculator extends AbstractPdkOrderOptionCalculator
{
    /**
     * @var \MyParcelNL\Pdk\App\Options\Helper\CarrierSettingsDefinitionHelper
     */
    private $carrierSettings;

    /**
     * @var \MyParcelNL\Pdk\App\Options\Helper\ProductSettingsDefinitionHelper
     */
    private $productSettings;

    /**
     * @var \MyParcelNL\Pdk\App\Options\Helper\ShipmentOptionsDefinitionHelper
     */
    private $shipmentOptions;

    /**
     * @var \MyParcelNL\Pdk\Types\Contract\TriStateServiceInterface
     */
    private $triStateService;

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrder $order
     */
    public function __construct(PdkOrder $order)
    {
        parent::__construct($order);

        $this->triStateService = Pdk::get(TriStateServiceInterface::class);

        $this->shipmentOptions = new ShipmentOptionsDefinitionHelper($this->order);
        $this->productSettings = new ProductSettingsDefinitionHelper($this->order);
        $this->carrierSettings = new CarrierSettingsDefinitionHelper($this->order);
    }

    /**
     * @return void
     */
    public function calculate(): void
    {
        /** @var \MyParcelNL\Pdk\Validation\Validator\CarrierSchema $schema */
        $schema = Pdk::get(CarrierSchema::class);

        $schema->setCarrier($this->order->deliveryOptions->carrier);

        /** @var OrderOptionDefinitionInterface[] $definitions */
        $definitions = Pdk::get('orderOptionDefinitions');

        foreach ($definitions as $definition) {
            $value = $this->triStateService->resolve(
                $this->shipmentOptions->get($definition),
                $this->productSettings->get($definition),
                $this->carrierSettings->get($definition)
            );

            $this->order->deliveryOptions->shipmentOptions->setAttribute($definition->getShipmentOptionsKey(), $value);
        }
    }
}
