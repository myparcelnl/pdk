<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Contract;

use MyParcelNL\Pdk\App\Order\Model\PdkOrder;

interface PdkOrderOptionCalculatorInterface
{
    /**
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrder $order
     */
    public function __construct(PdkOrder $order);

    /**
     * @return void
     */
    public function calculate(): void;
}
