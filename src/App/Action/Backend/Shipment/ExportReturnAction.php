<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Shipment;

use MyParcelNL\Pdk\App\Action\Backend\Order\AbstractOrderAction;
use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ExportReturnAction extends AbstractOrderAction
{
    public function __construct(
        PdkOrderRepositoryInterface         $pdkOrderRepository,
        private readonly ShipmentRepository $shipmentRepository
    ) {
        parent::__construct($pdkOrderRepository);
    }

    /**
     * @throws \Exception
     */
    public function handle(Request $request): Response
    {
        $orders    = $this->pdkOrderRepository->getMany($this->getOrderIds($request));
        $shipments = $orders->getLastShipments();
        $shipments = $this->shipmentRepository->createReturnShipments($shipments);

        $orders->updateShipments($shipments);

        $this->pdkOrderRepository->updateMany($orders);

        $orderIds = $orders->pluck('externalIdentifier')
            ->all();

        // todo only return shipments that are created
        return Actions::execute(PdkBackendActions::FETCH_ORDERS, ['orderIds' => $orderIds]);
    }
}
