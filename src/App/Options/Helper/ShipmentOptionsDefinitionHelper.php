<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Options\Helper;

use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;

final class ShipmentOptionsDefinitionHelper extends AbstractOptionDefinitionHelper
{
    protected function getDefinitionKey(OrderOptionDefinitionInterface $definition): ?string
    {
        return $definition->getShipmentOptionsKey();
    }

    /**
     * @return mixed
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    protected function getValue(string $attribute)
    {
        return $this->order->deliveryOptions->shipmentOptions->getAttribute($attribute);
    }
}
