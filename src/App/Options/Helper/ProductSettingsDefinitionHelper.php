<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Options\Helper;

use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;

final class ProductSettingsDefinitionHelper extends AbstractOptionDefinitionHelper
{
    protected function getDefinitionKey(OrderOptionDefinitionInterface $definition): ?string
    {
        return $definition->getProductSettingsKey();
    }

    /**
     * @return mixed
     */
    protected function getValue(string $attribute)
    {
        $values = $this->order->lines
            ->pluck(sprintf('product.mergedSettings.%s', $attribute))
            ->all();

        return $this->triStateService->coerce(...$values);
    }
}
