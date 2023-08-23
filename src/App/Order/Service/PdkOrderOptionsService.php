<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Service;

use MyParcelNL\Pdk\App\Order\Calculator\PdkOrderCalculator;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;

class PdkOrderOptionsService implements PdkOrderOptionsServiceInterface
{
    /**
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrder $order
     *
     * @return \MyParcelNL\Pdk\App\Order\Model\PdkOrder
     */
    public function calculate(PdkOrder $order): PdkOrder
    {
        return (new PdkOrderCalculator($order))->calculateAll();
    }
}
