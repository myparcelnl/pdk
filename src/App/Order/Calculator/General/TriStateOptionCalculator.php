<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\General;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Facade\Pdk;

final class TriStateOptionCalculator extends AbstractPdkOrderOptionCalculator
{
    /**
     * @var mixed|\MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface
     */
    private $orderOptionsService;

    public function __construct(PdkOrder $order)
    {
        parent::__construct($order);

        $this->orderOptionsService = Pdk::get(PdkOrderOptionsServiceInterface::class);
    }

    public function calculate(): void
    {
        $this->orderOptionsService->calculateShipmentOptions($this->order);
    }
}
