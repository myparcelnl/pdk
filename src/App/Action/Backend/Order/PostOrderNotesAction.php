<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Order;

use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\PdkOrderNote;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Fulfilment\Repository\OrderNotesRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PostOrderNotesAction extends AbstractOrderAction
{
    /**
     * @var \MyParcelNL\Pdk\Fulfilment\Repository\OrderNotesRepository
     */
    private $orderNotesRepository;

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface $pdkOrderRepository
     * @param  \MyParcelNL\Pdk\Fulfilment\Repository\OrderNotesRepository     $orderNotesRepository
     */
    public function __construct(
        PdkOrderRepositoryInterface $pdkOrderRepository,
        OrderNotesRepository        $orderNotesRepository
    ) {
        parent::__construct($pdkOrderRepository);
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
        // TODO: Remove this and check if shop subscription allows using order notes
        if (! $request->query->has('OVERRIDE')) {
            return $this->getFetchOrdersResponse($request);
        }

        $orderIds = $this->getOrderIds($request);
        $orders   = $this->pdkOrderRepository->getMany($orderIds);

        $orders
            ->filter(function (PdkOrder $order) {
                return $order->apiIdentifier && $order->notes->isNotEmpty();
            })
            ->each(function (PdkOrder $order) {
                $notes = $this->orderNotesRepository->postOrderNotes(
                    $order->apiIdentifier,
                    $order->notes->filter(function (PdkOrderNote $note) {
                        return null === $note->apiIdentifier;
                    })
                        ->toFulfilmentCollection()
                );
                $order->notes->addApiIdentifiers($notes);
            });

        $this->pdkOrderRepository->updateMany($orders);

        return $this->getFetchOrdersResponse($request);
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function getFetchOrdersResponse(Request $request): Response
    {
        return Actions::execute(PdkBackendActions::FETCH_ORDERS, ['orderIds' => $this->getOrderIds($request)]);
    }
}

