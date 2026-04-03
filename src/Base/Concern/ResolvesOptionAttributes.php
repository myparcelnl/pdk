<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Concern;

use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\Facade\Pdk;

trait ResolvesOptionAttributes
{
    /**
     * Build an array of attributes from registered option definitions.
     *
     * @param  callable(OrderOptionDefinitionInterface): ?string $keyExtractor
     * @param  mixed                                             $default
     *
     * @return array
     */
    protected function resolveOptionAttributes(callable $keyExtractor, $default): array
    {
        /** @var OrderOptionDefinitionInterface[] $definitions */
        $definitions = Pdk::get('orderOptionDefinitions');
        $attributes  = [];

        foreach ($definitions as $definition) {
            $key = $keyExtractor($definition);

            if ($key !== null) {
                $attributes[$key] = $default;
            }
        }

        return $attributes;
    }
}
