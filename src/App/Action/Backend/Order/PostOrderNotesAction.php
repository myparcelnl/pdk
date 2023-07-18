<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Order;

use MyParcelNL\Pdk\Api\Response\JsonResponse;
use MyParcelNL\Pdk\App\Action\Contract\ActionInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Fulfilment\Repository\OrderNotesRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PostOrderNotesAction implements ActionInterface
{
    /**
     * @var \MyParcelNL\Pdk\Fulfilment\Repository\OrderNotesRepository
     */
    private $orderNotesRepository;

    /**
     * @param  \MyParcelNL\Pdk\Fulfilment\Repository\OrderNotesRepository $orderNotesRepository
     */
    public function __construct(
        OrderNotesRepository        $orderNotesRepository
    ) {
        $this->orderNotesRepository = $orderNotesRepository;
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function handle(Request $request): Response
    {
        /** @var \MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection $orders */
        $orders = $request->get('orders');

        $orders->each(function (PdkOrder $order) {
            if ($order->orderNotes) {
                $this->orderNotesRepository->postOrderNotes(
                    $order->orderNotes,
                    $order->apiIdentifier
                );
            }
        });

        return new JsonResponse([
            'orders' => $orders,
        ]);
    }
}

