<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Action\Order;

use MyParcelNL\Pdk\Plugin\Action\ActionInterface;
use MyParcelNL\Pdk\Plugin\Repository\ApiPdkOrderRepository;

abstract class AbstractOrderAction implements ActionInterface
{
    /**
     * @var \MyParcelNL\Pdk\Plugin\Repository\ApiPdkOrderRepository
     */
    protected $orderRepository;

    public function __construct(ApiPdkOrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }
}
