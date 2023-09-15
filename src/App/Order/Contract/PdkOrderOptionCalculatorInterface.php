<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Contract;

use MyParcelNL\Pdk\App\Order\Model\PdkOrder;

interface PdkOrderOptionCalculatorInterface
{
    public function __construct(PdkOrder $order);

    public function calculate(): void;
}
