<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Options\Helper;

use MyParcelNL\Pdk\App\Options\Contract\OptionDefinitionHelperInterface;
use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Types\Service\TriStateService;

abstract class AbstractOptionDefinitionHelper implements OptionDefinitionHelperInterface
{
    /**
     * @var \MyParcelNL\Pdk\App\Order\Model\PdkOrder
     */
    protected $order;

    /**
     * @var \MyParcelNL\Pdk\Types\Service\TriStateService
     */
    protected $triStateService;

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrder $order
     */
    public function __construct(PdkOrder $order)
    {
        $this->order = $order;

        $this->triStateService = Pdk::get(TriStateService::class);
    }

    /**
     * @param  \MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface $definition
     *
     * @return null|string
     */
    abstract protected function getDefinitionKey(OrderOptionDefinitionInterface $definition): ?string;

    /**
     * @param  string $attribute
     *
     * @return mixed
     */
    abstract protected function getValue(string $attribute);

    /**
     * @param  \MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface $definition
     *
     * @return mixed
     */
    public function get(OrderOptionDefinitionInterface $definition)
    {
        $definitionKey = $this->getDefinitionKey($definition);

        if (null === $definitionKey) {
            return null;
        }

        $value = $this->getValue($definitionKey);

        return $this->triStateService->coerce($value);
    }
}

