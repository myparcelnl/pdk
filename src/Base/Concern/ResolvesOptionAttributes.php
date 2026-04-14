<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Concern;

use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\Facade\Pdk;

trait ResolvesOptionAttributes
{
    /**
     * Build arrays of attributes and casts from registered option definitions.
     *
     * @param  callable(OrderOptionDefinitionInterface): ?string $keyExtractor
     * @param  mixed                                             $default
     * @param  callable(OrderOptionDefinitionInterface): string  $castExtractor
     *
     * @return array{0: array, 1: array} [$attributes, $casts]
     */
    protected function resolveOptionAttributes(callable $keyExtractor, $default, callable $castExtractor): array
    {
        /** @var OrderOptionDefinitionInterface[] $definitions */
        $definitions = Pdk::get('orderOptionDefinitions');
        $attributes  = [];
        $casts       = [];

        foreach ($definitions as $definition) {
            $key = $keyExtractor($definition);

            if ($key !== null) {
                $attributes[$key] = $default;
                $casts[$key]      = $castExtractor($definition);
            }
        }

        return [$attributes, $casts];
    }
}
