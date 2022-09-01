<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Action\Order;

use MyParcelNL\Pdk\Api\Response\JsonResponse;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Plugin\Service\ContextServiceInterface;
use Symfony\Component\HttpFoundation\Response;

class GetOrderDataAction extends AbstractOrderAction
{
    /**
     * @param  array $parameters
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(array $parameters): Response
    {
        $collection = $this->orderRepository->getMany($parameters['orderIds']);

        /** @var \MyParcelNL\Pdk\Plugin\Service\ContextServiceInterface $contextService */
        $contextService = Pdk::get(ContextServiceInterface::class);

        return new JsonResponse([
            'orders' => $contextService
                ->createOrderDataContext($collection)
                ->toArray(),
        ]);
    }
}
