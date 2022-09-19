<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Action\Order;

use MyParcelNL\Pdk\Plugin\Action\ActionInterface;
use MyParcelNL\Pdk\Plugin\Repository\AbstractPdkOrderRepository;

abstract class AbstractOrderAction implements ActionInterface
{
    /**
     * @var \MyParcelNL\Pdk\Plugin\Repository\AbstractPdkOrderRepository
     */
    protected $orderRepository;

    public function __construct(AbstractPdkOrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }
}
