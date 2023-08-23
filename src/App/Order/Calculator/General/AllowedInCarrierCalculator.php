<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\General;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use MyParcelNL\Pdk\Validation\Validator\CarrierSchema;

/**
 * Disables options that are not allowed based on the carrier schema.
 */
final class AllowedInCarrierCalculator extends AbstractPdkOrderOptionCalculator
{
    /**
     * @var \MyParcelNL\Pdk\Validation\Validator\CarrierSchema
     */
    private $carrierSchema;

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrder $order
     */
    public function __construct(PdkOrder $order)
    {
        parent::__construct($order);

        /** @var \MyParcelNL\Pdk\Validation\Validator\CarrierSchema $schema */
        $schema = Pdk::get(CarrierSchema::class);

        $this->carrierSchema = $schema->setCarrier($this->order->deliveryOptions->carrier);
    }

    /**
     * @return void
     */
    public function calculate(): void
    {
        /** @var \MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface[] $definitions */
        $definitions = Pdk::get('orderOptionDefinitions');

        foreach ($definitions as $definition) {
            if ($definition->validate($this->carrierSchema)) {
                continue;
            }

            $this->order->deliveryOptions->shipmentOptions->setAttribute(
                $definition->getShipmentOptionsKey(),
                TriStateService::DISABLED
            );
        }
    }
}
