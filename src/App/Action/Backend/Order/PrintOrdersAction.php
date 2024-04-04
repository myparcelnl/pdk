<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Order;

use MyParcelNL\Pdk\App\Action\Backend\Shipment\PrintShipmentsAction;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PrintOrdersAction extends PrintShipmentsAction
{
    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request): Response
    {
        $orderIds = $this->getOrderIds($request);
        $orders   = $this->pdkOrderRepository->getMany($orderIds);

        $shipmentIds = $orders->getAllShipments()
            ->pluck('id')
            ->all();

        $request->query->set('shipmentIds', implode(';', $shipmentIds));

        return parent::handle($request);
    }
}
