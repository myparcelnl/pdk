<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Options\Helper;

use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;

final class ShipmentOptionsDefinitionHelper extends AbstractOptionDefinitionHelper
{
    /**
     * @param  \MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface $definition
     *
     * @return null|string
     */
    protected function getDefinitionKey(OrderOptionDefinitionInterface $definition): ?string
    {
        return $definition->getShipmentOptionsKey();
    }

    /**
     * @param  string $attribute
     *
     * @return mixed
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    protected function getValue(string $attribute)
    {
        return $this->order->deliveryOptions->shipmentOptions->getAttribute($attribute);
    }
}
