<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Order;

use MyParcelNL\Pdk\App\Action\Contract\ActionInterface;
use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection;
use MyParcelNL\Pdk\Fulfilment\Model\Order;
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
        OrderNotesRepository $orderNotesRepository
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
        $data            = json_decode($request->getContent(), true);
        $orderCollection = new OrderCollection($data['data']['orders'] ?? []);

        $orderCollection->each(function (Order $order) {
            if ($order->orderNotes->isNotEmpty()) {
                $this->orderNotesRepository->postOrderNotes($order->orderNotes, $order->uuid);
            }
        });

        return Actions::execute(PdkBackendActions::FETCH_ORDERS, [
            'orderIds' => $orderCollection->pluck('externalIdentifier')->toArray()
        ]);
    }
}

