<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator;

abstract class AbstractCarrierOptionsCalculator extends AbstractPdkOrderOptionCalculator
{
    /**
     * @return array<class-string<\MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionCalculatorInterface>>
     */
    abstract protected function getCalculators(): array;

    public function calculate(): void
    {
        foreach ($this->getCalculators() as $calculator) {
            /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionCalculatorInterface $calculator */
            $calculator = new $calculator($this->order);

            $calculator->calculate();
        }
    }
}
