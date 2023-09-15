<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Order;

use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\Facade\Actions;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateOrderAction extends AbstractOrderAction
{
    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request): Response
    {
        $orderIds = $this->getOrderIds($request);
        $status   = $request->get('status');

        if (! $status) {
            return new Response('No status to update', Response::HTTP_OK);
        }

        $this->orderStatusService->updateStatus($orderIds, $status);

        return Actions::execute(PdkBackendActions::FETCH_ORDERS);
    }
}
