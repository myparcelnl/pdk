<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Options\Helper;

use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;

final class CarrierSettingsDefinitionHelper extends AbstractOptionDefinitionHelper
{
    private ?CarrierSettings $model = null;

    protected function getDefinitionKey(OrderOptionDefinitionInterface $definition): ?string
    {
        return $definition->getCarrierSettingsKey();
    }

    /**
     * @return mixed
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    protected function getValue(string $attribute)
    {
        if (! $this->model) {
            $this->model = CarrierSettings::fromCarrier($this->order->deliveryOptions->carrier);
        }

        return $this->model->getAttribute($attribute);
    }
}
