<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator;

use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Facade\Pdk;

final class PdkOrderCalculator
{
    /**
     * @var \MyParcelNL\Pdk\App\Order\Model\PdkOrder
     */
    private $order;

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrder $order
     */
    public function __construct(PdkOrder $order)
    {
        $this->order = clone $order;
    }

    /**
     * @return \MyParcelNL\Pdk\App\Order\Model\PdkOrder
     */
    public function calculateAll(): PdkOrder
    {
        foreach ($this->getCalculators() as $class) {
            /** @var \MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator $calculator */
            $calculator = new $class($this->order);

            $calculator->calculate();
        }
        $__AAAAA = $this->order->customsDeclaration->items[0];

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
