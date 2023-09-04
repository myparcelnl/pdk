<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Options\Contract;

interface OptionDefinitionHelperInterface
{
    /**
     * @param  \MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface $definition
     *
     * @return mixed|int
     */
    public function get(OrderOptionDefinitionInterface $definition);
}
