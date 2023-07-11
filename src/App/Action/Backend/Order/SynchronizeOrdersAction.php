<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Order;

use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Fulfilment\Repository\OrderRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class SynchronizeOrdersAction extends AbstractOrderAction
{
    /**
     * @var \MyParcelNL\Pdk\Fulfilment\Repository\OrderRepository
     */
    private $orderRepository;

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface $pdkOrderRepository
     * @param  \MyParcelNL\Pdk\Fulfilment\Repository\OrderRepository          $orderRepository
     */
    public function __construct(PdkOrderRepositoryInterface $pdkOrderRepository, OrderRepository $orderRepository)
    {
        parent::__construct($pdkOrderRepository);
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request): Response
    {
        $orderIds = $this->getOrderIds($request);
        $orders   = $this->pdkOrderRepository->getMany($orderIds);

        $apiOrders = $this->orderRepository->query(['uuid' => implode(',', $orderIds)]);

        /** @var \MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection $mergedOrders */
        $mergedOrders = $orders->mergeByKey($apiOrders, 'uuid');

        $this->pdkOrderRepository->updateMany($mergedOrders);

        return Actions::execute($request, ['action' => PdkBackendActions::FETCH_ORDERS]);
    }
}
