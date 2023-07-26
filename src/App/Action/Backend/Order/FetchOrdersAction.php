<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Order;

use MyParcelNL\Pdk\Api\Response\JsonResponse;
use MyParcelNL\Pdk\Context\Contract\ContextServiceInterface;
use MyParcelNL\Pdk\Facade\Pdk;
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

        /** @var \MyParcelNL\Pdk\Context\Contract\ContextServiceInterface $contextService */
        $contextService = Pdk::get(ContextServiceInterface::class);

        return new JsonResponse([
            'orders' => $contextService
                ->createOrderDataContext($collection)
                ->toArrayWithoutNull(),
        ]);
    }
}
