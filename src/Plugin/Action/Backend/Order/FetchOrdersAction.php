<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Action\Backend\Order;

use MyParcelNL\Pdk\Api\Response\JsonResponse;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Plugin\Service\ContextServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FetchOrdersAction extends AbstractOrderAction
{
    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request): Response
    {
        $orderIds   = $this->getOrderIds($request);
        $collection = $this->pdkOrderRepository->getMany($orderIds);

        /** @var \MyParcelNL\Pdk\Plugin\Service\ContextServiceInterface $contextService */
        $contextService = Pdk::get(ContextServiceInterface::class);

        return new JsonResponse([
            'orders' => $contextService
                ->createOrderDataContext($collection)
                ->toArray(),
        ]);
    }
}
