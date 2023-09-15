<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator;

use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Facade\Pdk;

final class PdkOrderCalculator
{
    private readonly PdkOrder $order;

    public function __construct(PdkOrder $order)
    {
        $this->order = clone $order;
    }

    public function calculateAll(): PdkOrder
    {
        foreach ($this->getCalculators() as $class) {
            /** @var \MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator $calculator */
            $calculator = new $class($this->order);

            $calculator->calculate();
        }

        return $this->order;
    }

    /**
     * @return class-string<\MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator>[]
     */
    protected function getCalculators(): array
    {
        return Pdk::get('orderCalculators');
    }
}
