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
    public function __construct(
        PdkOrderRepositoryInterface                      $pdkOrderRepository,
        private readonly PdkOrderNoteRepositoryInterface $pdkOrderNoteRepository,
        private readonly OrderNotesRepository            $orderNotesRepository
    ) {
        parent::__construct($pdkOrderRepository);
    }

    /**
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
            ->filter(fn(PdkOrder $order) => $order->apiIdentifier && $order->notes->isNotEmpty())
            ->each(function (PdkOrder $order) {
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

    protected function getFetchOrdersResponse(Request $request): Response
    {
        return Actions::execute(PdkBackendActions::FETCH_ORDERS, ['orderIds' => $this->getOrderIds($request)]);
    }
}

