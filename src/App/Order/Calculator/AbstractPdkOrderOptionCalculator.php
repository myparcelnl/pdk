<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator;

use MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionCalculatorInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;

abstract class AbstractPdkOrderOptionCalculator implements PdkOrderOptionCalculatorInterface
{
    /**
     * @var \MyParcelNL\Pdk\App\Order\Model\PdkOrder
     */
    protected $order;

    public function __construct(PdkOrder $order)
    {
        $this->order = $order;
    }
}
