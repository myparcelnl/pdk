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
    public function __construct(PdkOrderRepositoryInterface      $pdkOrderRepository,
                                private readonly OrderRepository $orderRepository
    ) {
        parent::__construct($pdkOrderRepository);
    }

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
