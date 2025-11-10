<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Order;

use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderNoteRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Facade\AccountSettings;
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
     * @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderNoteRepositoryInterface
     */
    private $pdkOrderNoteRepository;

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface     $pdkOrderRepository
     * @param  \MyParcelNL\Pdk\App\Order\Contract\PdkOrderNoteRepositoryInterface $pdkOrderNoteRepository
     * @param  \MyParcelNL\Pdk\Fulfilment\Repository\OrderNotesRepository         $orderNotesRepository
     */
    public function __construct(
        PdkOrderRepositoryInterface     $pdkOrderRepository,
        PdkOrderNoteRepositoryInterface $pdkOrderNoteRepository,
        OrderNotesRepository            $orderNotesRepository
    ) {
        parent::__construct($pdkOrderRepository);
        $this->pdkOrderNoteRepository = $pdkOrderNoteRepository;
        $this->orderNotesRepository   = $orderNotesRepository;
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function handle(Request $request): Response
    {
        if (! AccountSettings::hasSubscriptionFeature(Account::FEATURE_ORDER_NOTES)) {
            return $this->getFetchOrdersResponse($request);
        }

        $orderIds = $this->getOrderIds($request);
        $orders   = $this->pdkOrderRepository->getMany($orderIds);

        $orders
            ->filter(function (PdkOrder $order) {
                return $order->apiIdentifier && $order->notes->where('apiIdentifier', '==', null)
                        ->toFulfilmentCollection()->isNotEmpty();
            })
            ->each(function (PdkOrder $order) {
                // todo: combine the ->where statements to a single call
                $notes = $this->orderNotesRepository->postOrderNotes(
                    $order->apiIdentifier,
                    $order->notes->where('apiIdentifier', '==', null)
                        ->toFulfilmentCollection()
                );

                $order->notes->addApiIdentifiers($notes);

                $this->pdkOrderNoteRepository->updateMany($order->notes);
            });

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

