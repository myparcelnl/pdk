<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Shipment;

use DateTime;
use MyParcelNL\Pdk\App\Action\Backend\Order\AbstractOrderAction;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DeleteShipmentsAction extends AbstractOrderAction
{
    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request): Response
    {
        $orderIds = $request->get('orderIds');
        $orders   = $this->pdkOrderRepository->getMany($orderIds);

        $shipmentIds = $this->getShipmentIds($request, $orders);

        $shipmentsToDelete = $this->markShipmentsForDeletion($orders, $shipmentIds);

        $orders->updateShipments($shipmentsToDelete);

        $this->pdkOrderRepository->updateMany($orders);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection $orders
     * @param  string[]                                                $shipmentIds
     *
     * @return \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection
     */
    protected function markShipmentsForDeletion(
        PdkOrderCollection $orders,
        array              $shipmentIds
    ): ShipmentCollection {
        return $orders->getAllShipments()
            ->whereIn('id', $shipmentIds)
            ->map(function (Shipment $shipment) {
                $shipment->deleted = new DateTime();
                $shipment->updated = null;

                return $shipment;
            });
    }
}

